<?php
namespace Core;

use Core\Database;
use PDO;

class SavedFilters
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(int $userId, string $module): array
    {
        $stmt = $this->db->prepare('SELECT id, name, filters_json FROM saved_filters WHERE user_id = :user AND module = :module ORDER BY name');
        $stmt->execute(['user' => $userId, 'module' => $module]);
        return $stmt->fetchAll();
    }

    public function save(int $userId, string $module, string $name, array $filters): void
    {
        $stmt = $this->db->prepare('INSERT INTO saved_filters (user_id, module, name, filters_json) VALUES (:user, :module, :name, :filters)');
        $stmt->execute([
            'user' => $userId,
            'module' => $module,
            'name' => $name,
            'filters' => json_encode($filters),
        ]);
    }
}
