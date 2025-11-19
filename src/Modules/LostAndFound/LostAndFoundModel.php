<?php
namespace Modules\LostAndFound;

use Core\Database;
use PDO;

class LostAndFoundModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(array $filters = []): array
    {
        $sql = 'SELECT * FROM lost_and_found_items WHERE deleted_at IS NULL';
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['query'])) {
            $sql .= ' AND (item_name LIKE :query OR location LIKE :query)';
            $params['query'] = '%' . $filters['query'] . '%';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO lost_and_found_items (item_name, category, status, location, description, found_at, created_by) VALUES (:item_name, :category, :status, :location, :description, :found_at, :user)');
        $stmt->execute([
            'item_name' => $data['item_name'],
            'category' => $data['category'] ?? 'general',
            'status' => $data['status'] ?? 'new',
            'location' => $data['location'] ?? '',
            'description' => $data['description'] ?? '',
            'found_at' => $data['found_at'] ?? date('Y-m-d'),
            'user' => $data['created_by'],
        ]);
        return (int)$this->db->lastInsertId();
    }
}
