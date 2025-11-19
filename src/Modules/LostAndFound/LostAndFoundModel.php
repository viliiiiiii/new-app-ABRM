<?php
namespace Modules\LostAndFound;

use Core\ActivityLogger;
use Core\Database;
use PDO;

class LostAndFoundModel
{
    private PDO $db;
    private ActivityLogger $logger;

    public function __construct(?array $user = null)
    {
        $this->db = Database::connection();
        $this->logger = new ActivityLogger($user);
    }

    public function all(array $filters = [], bool $includeDeleted = false): array
    {
        $sql = 'SELECT * FROM lost_and_found_items WHERE 1=1';
        $params = [];
        if (!$includeDeleted) {
            $sql .= ' AND deleted_at IS NULL';
        }
        if (!empty($filters['state'])) {
            $sql .= ' AND lifecycle_state = :state';
            $params['state'] = $filters['state'];
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND category = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['tag'])) {
            $sql .= ' AND FIND_IN_SET(:tag, tags)';
            $params['tag'] = $filters['tag'];
        }
        if (!empty($filters['query'])) {
            $sql .= ' AND (item_name LIKE :query OR description LIKE :query OR owner_name LIKE :query)';
            $params['query'] = '%' . $filters['query'] . '%';
        }
        if (!empty($filters['from'])) {
            $sql .= ' AND found_at >= :from';
            $params['from'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND found_at <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }
        if (!empty($filters['high_value'])) {
            $sql .= ' AND high_value = 1';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO lost_and_found_items (item_code, item_name, category, tags, lifecycle_state, status, location_area, location_building, location_floor, location_exact, owner_name, owner_contact, owner_status, reminder_date, retention_date, high_value, sensitive_document, description, notes, found_at, created_by, updated_by) VALUES (:item_code, :item_name, :category, :tags, :state, :status, :area, :building, :floor, :exact, :owner_name, :owner_contact, :owner_status, :reminder_date, :retention_date, :high_value, :sensitive_document, :description, :notes, :found_at, :created_by, :created_by)');
        $stmt->execute([
            'item_code' => $data['item_code'],
            'item_name' => $data['item_name'],
            'category' => $data['category'] ?? null,
            'tags' => $data['tags'] ?? null,
            'state' => $data['lifecycle_state'] ?? 'new',
            'status' => $data['status'] ?? 'open',
            'area' => $data['location_area'] ?? null,
            'building' => $data['location_building'] ?? null,
            'floor' => $data['location_floor'] ?? null,
            'exact' => $data['location_exact'] ?? null,
            'owner_name' => $data['owner_name'] ?? null,
            'owner_contact' => $data['owner_contact'] ?? null,
            'owner_status' => $data['owner_status'] ?? 'unknown',
            'reminder_date' => $data['reminder_date'] ?? null,
            'retention_date' => $data['retention_date'] ?? null,
            'high_value' => !empty($data['high_value']) ? 1 : 0,
            'sensitive_document' => !empty($data['sensitive_document']) ? 1 : 0,
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
            'found_at' => $data['found_at'] ?? date('Y-m-d H:i:s'),
            'created_by' => $data['created_by'],
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->logState($id, $data['lifecycle_state'] ?? 'new', $data['created_by'], 'Item created');
        $this->logger->log('lost_and_found', 'create', $id, null, $data, 'Item created');
        return $id;
    }

    public function changeState(int $id, string $state, int $userId, string $notes = ''): void
    {
        $stmt = $this->db->prepare('UPDATE lost_and_found_items SET lifecycle_state = :state, updated_by = :user WHERE id = :id');
        $stmt->execute(['state' => $state, 'user' => $userId, 'id' => $id]);
        $this->logState($id, $state, $userId, $notes);
        $this->logger->log('lost_and_found', 'state_change', $id, null, ['state' => $state], $notes);
    }

    public function softDelete(int $id, int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE lost_and_found_items SET deleted_at = NOW(), updated_by = :user WHERE id = :id');
        $stmt->execute(['user' => $userId, 'id' => $id]);
        $this->logger->log('lost_and_found', 'soft_delete', $id);
    }

    public function restore(int $id, int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE lost_and_found_items SET deleted_at = NULL, updated_by = :user WHERE id = :id');
        $stmt->execute(['user' => $userId, 'id' => $id]);
        $this->logger->log('lost_and_found', 'restore', $id);
    }

    public function release(int $id, array $data): void
    {
        $item = $this->find($id);
        $stmt = $this->db->prepare('INSERT INTO lost_and_found_release_forms (item_id, recipient_name, recipient_id, recipient_contact, staff_name, staff_signature_path, recipient_signature_path) VALUES (:item_id, :recipient_name, :recipient_id, :recipient_contact, :staff_name, :staff_sig, :recipient_sig)');
        $stmt->execute([
            'item_id' => $id,
            'recipient_name' => $data['recipient_name'],
            'recipient_id' => $data['recipient_id'] ?? null,
            'recipient_contact' => $data['recipient_contact'] ?? null,
            'staff_name' => $data['staff_name'] ?? '',
            'staff_sig' => $data['staff_signature_path'] ?? null,
            'recipient_sig' => $data['recipient_signature_path'] ?? null,
        ]);
        $stmt = $this->db->prepare('UPDATE lost_and_found_items SET lifecycle_state = "released", released_at = NOW(), updated_by = :user WHERE id = :id');
        $stmt->execute(['user' => $data['user_id'], 'id' => $id]);
        $this->logState($id, 'released', $data['user_id'], 'Released to recipient');
        $options = [];
        if ($item && !empty($item['high_value'])) {
            $options = [
                'severity' => 'critical',
                'alert' => true,
                'alert_message' => 'High value item ' . ($item['item_code'] ?? ('#' . $id)) . ' released',
                'alert_link' => '/lost-and-found?history=' . $id,
            ];
        }
        $this->logger->log('lost_and_found', 'release', $id, $item, $data, 'Item released', $options);
    }

    public function history(int $id): array
    {
        $states = $this->db->prepare('SELECT * FROM lost_and_found_states WHERE item_id = :id ORDER BY changed_at DESC');
        $states->execute(['id' => $id]);
        $versions = $this->db->prepare('SELECT * FROM lost_and_found_versions WHERE item_id = :id ORDER BY changed_at DESC');
        $versions->execute(['id' => $id]);
        return [
            'states' => $states->fetchAll(),
            'versions' => $versions->fetchAll(),
        ];
    }

    public function updateTextFields(int $id, array $fields, int $userId): void
    {
        $item = $this->find($id);
        if (!$item) {
            return;
        }
        $set = [];
        $params = ['id' => $id];
        foreach (['description', 'notes'] as $field) {
            if (array_key_exists($field, $fields)) {
                $set[] = "$field = :$field";
                $params[$field] = $fields[$field];
                if ($item[$field] !== $fields[$field]) {
                    $this->recordVersion($id, $field, $item[$field], $fields[$field], $userId);
                }
            }
        }
        if ($set) {
            $sql = 'UPDATE lost_and_found_items SET ' . implode(', ', $set) . ', updated_by = :user WHERE id = :id';
            $params['user'] = $userId;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $this->logger->log('lost_and_found', 'update', $id, $item, $fields, 'Item text updated');
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM lost_and_found_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function dueReminders(): array
    {
        $stmt = $this->db->query('SELECT item_name, reminder_date FROM lost_and_found_items WHERE reminder_date <= CURDATE() AND deleted_at IS NULL AND lifecycle_state NOT IN ("released","archived")');
        return $stmt->fetchAll();
    }

    public function stats(): array
    {
        $stmt = $this->db->query('SELECT lifecycle_state, COUNT(*) AS total FROM lost_and_found_items WHERE deleted_at IS NULL GROUP BY lifecycle_state');
        return $stmt->fetchAll();
    }

    private function logState(int $itemId, string $state, int $userId, string $notes = ''): void
    {
        $stmt = $this->db->prepare('INSERT INTO lost_and_found_states (item_id, state, changed_by, notes) VALUES (:item_id, :state, :user, :notes)');
        $stmt->execute([
            'item_id' => $itemId,
            'state' => $state,
            'user' => $userId,
            'notes' => $notes,
        ]);
    }

    private function recordVersion(int $itemId, string $field, ?string $oldValue, ?string $newValue, int $userId): void
    {
        $stmt = $this->db->prepare('INSERT INTO lost_and_found_versions (item_id, field, old_value, new_value, changed_by) VALUES (:item_id, :field, :old_value, :new_value, :user)');
        $stmt->execute([
            'item_id' => $itemId,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user' => $userId,
        ]);
    }
}
