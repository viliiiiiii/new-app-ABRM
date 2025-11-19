<?php
namespace Modules\Notes;

use Core\Database;
use PDO;

class NotesModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function list(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM notes WHERE owner_id = :user AND deleted_at IS NULL ORDER BY updated_at DESC');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }
}
