<?php
namespace Modules\Notes;

use Core\ActivityLogger;
use Core\Database;
use Core\Notifications;
use PDO;

class NotesModel
{
    private PDO $db;
    private ActivityLogger $logger;
    private Notifications $notifications;

    public function __construct(?array $user = null)
    {
        $this->db = Database::connection();
        $this->logger = new ActivityLogger($user);
        $this->notifications = new Notifications();
    }

    public function list(int $userId, array $filters = []): array
    {
        $sql = 'SELECT DISTINCT n.* FROM notes n LEFT JOIN note_shares s ON s.note_id = n.id WHERE (n.owner_id = :user OR s.user_id = :user OR s.sector_id = :sector) AND n.deleted_at IS NULL';
        $params = ['user' => $userId, 'sector' => $filters['sector_id'] ?? 0];
        if (!empty($filters['type'])) {
            $sql .= ' AND n.note_type = :type';
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['tag'])) {
            $sql .= ' AND FIND_IN_SET(:tag, n.tags)';
            $params['tag'] = $filters['tag'];
        }
        if (!empty($filters['pinned'])) {
            $sql .= ' AND n.pinned = 1';
        }
        if (!empty($filters['favourite'])) {
            $sql .= ' AND n.is_favourite = 1';
        }
        if (!empty($filters['incomplete'])) {
            $sql .= ' AND EXISTS (SELECT 1 FROM note_checklist_items c WHERE c.note_id = n.id AND c.is_done = 0)';
        }
        $sql .= ' ORDER BY n.pinned DESC, n.updated_at DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function checklist(int $noteId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM note_checklist_items WHERE note_id = :id ORDER BY position');
        $stmt->execute(['id' => $noteId]);
        return $stmt->fetchAll();
    }

    public function comments(int $noteId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM note_comments WHERE note_id = :id AND deleted_at IS NULL ORDER BY created_at');
        $stmt->execute(['id' => $noteId]);
        return $stmt->fetchAll();
    }

    public function readers(int $noteId): array
    {
        $stmt = $this->db->prepare('SELECT nr.user_id, u.name, nr.read_at FROM note_reads nr JOIN users u ON u.id = nr.user_id WHERE nr.note_id = :id ORDER BY nr.read_at DESC');
        $stmt->execute(['id' => $noteId]);
        return $stmt->fetchAll();
    }

    public function create(array $data, array $checklist = []): int
    {
        $stmt = $this->db->prepare('INSERT INTO notes (owner_id, title, body, note_type, reminder_at, pinned, is_favourite, tags) VALUES (:owner, :title, :body, :type, :reminder, :pinned, :favourite, :tags)');
        $stmt->execute([
            'owner' => $data['owner_id'],
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['note_type'],
            'reminder' => $data['reminder_at'],
            'pinned' => $data['pinned'],
            'favourite' => $data['is_favourite'],
            'tags' => $data['tags'],
        ]);
        $noteId = (int)$this->db->lastInsertId();
        $position = 0;
        foreach ($checklist as $item) {
            if (!$item) continue;
            $this->db->prepare('INSERT INTO note_checklist_items (note_id, description, position) VALUES (:note, :description, :position)')->execute([
                'note' => $noteId,
                'description' => $item,
                'position' => $position++,
            ]);
        }
        $this->logger->log('notes', 'create', $noteId, null, $data, 'Note created');
        $this->handleMentions($noteId, $data['body']);
        return $noteId;
    }

    public function toggleChecklist(int $itemId, bool $done): void
    {
        $stmt = $this->db->prepare('UPDATE note_checklist_items SET is_done = :done WHERE id = :id');
        $stmt->execute(['done' => $done ? 1 : 0, 'id' => $itemId]);
    }

    public function addComment(int $noteId, int $userId, string $body): void
    {
        $stmt = $this->db->prepare('INSERT INTO note_comments (note_id, author_id, body) VALUES (:note, :author, :body)');
        $stmt->execute(['note' => $noteId, 'author' => $userId, 'body' => $body]);
        $this->logger->log('notes', 'comment', $noteId, null, ['body' => $body]);
        $this->handleMentions($noteId, $body);
    }

    public function share(int $noteId, array $userIds = [], array $sectorIds = []): void
    {
        foreach ($userIds as $userId) {
            $this->db->prepare('INSERT INTO note_shares (note_id, user_id, share_type) VALUES (:note, :user, "user")')->execute(['note' => $noteId, 'user' => $userId]);
            $this->notifications->create($userId, 'A note was shared with you', '/notes?note=' . $noteId, 'info');
        }
        foreach ($sectorIds as $sectorId) {
            $this->db->prepare('INSERT INTO note_shares (note_id, sector_id, share_type) VALUES (:note, :sector, "sector")')->execute(['note' => $noteId, 'sector' => $sectorId]);
        }
    }

    public function pin(int $noteId, bool $pinned): void
    {
        $stmt = $this->db->prepare('UPDATE notes SET pinned = :pinned WHERE id = :id');
        $stmt->execute(['pinned' => $pinned ? 1 : 0, 'id' => $noteId]);
    }

    public function favourite(int $noteId, bool $fav): void
    {
        $stmt = $this->db->prepare('UPDATE notes SET is_favourite = :fav WHERE id = :id');
        $stmt->execute(['fav' => $fav ? 1 : 0, 'id' => $noteId]);
    }

    public function templates(): array
    {
        $stmt = $this->db->query('SELECT * FROM note_templates ORDER BY title');
        return $stmt->fetchAll();
    }

    private function handleMentions(int $noteId, string $body): void
    {
        if (!preg_match_all('/@([\w.\-]+@[\w.\-]+)/', $body, $matches)) {
            return;
        }
        $emails = array_unique($matches[1]);
        if (!$emails) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($emails), '?'));
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email IN (' . $placeholders . ')');
        $stmt->execute($emails);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $userId) {
            $this->db->prepare('INSERT INTO note_mentions (note_id, mentioned_user_id) VALUES (:note, :user)')->execute(['note' => $noteId, 'user' => $userId]);
            $this->notifications->create((int)$userId, 'You were mentioned in a note', '/notes?note=' . $noteId, 'info');
        }
    }

    public function markRead(int $noteId, int $userId): void
    {
        $stmt = $this->db->prepare('INSERT INTO note_reads (note_id, user_id, read_at) VALUES (:note, :user, NOW())');
        try {
            $stmt->execute(['note' => $noteId, 'user' => $userId]);
        } catch (\PDOException $e) {
            $this->db->prepare('UPDATE note_reads SET read_at = NOW() WHERE note_id = :note AND user_id = :user')->execute(['note' => $noteId, 'user' => $userId]);
        }
    }
}
