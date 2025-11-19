<?php
namespace Core;

use Core\Database;
use PDO;

class Notifications
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function latest(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = :user ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user AND is_read = 0');
        $stmt->execute(['user' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function create(int $userId, string $message, ?string $link = null, string $type = 'info'): void
    {
        $stmt = $this->db->prepare('INSERT INTO notifications (user_id, message, link, type) VALUES (:user, :message, :link, :type)');
        $stmt->execute([
            'user' => $userId,
            'message' => $message,
            'link' => $link,
            'type' => $type,
        ]);
    }
}
