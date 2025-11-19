<?php
namespace Core;

use Core\Database;
use PDO;

class Permissions
{
    private ?array $user;
    private PDO $db;
    private array $cache = [];

    public function __construct(?array $user)
    {
        $this->user = $user;
        $this->db = Database::connection();
    }

    public function allows(string $permission): bool
    {
        if (!$this->user) {
            return false;
        }
        if ($this->user['role_key'] === 'app_owner') {
            return true;
        }
        if (!isset($this->cache[$permission])) {
            $stmt = $this->db->prepare('SELECT 1 FROM effective_permissions WHERE user_id = :user AND permission_key = :perm');
            $stmt->execute(['user' => $this->user['id'], 'perm' => $permission]);
            $this->cache[$permission] = (bool)$stmt->fetchColumn();
        }
        return $this->cache[$permission];
    }
}
