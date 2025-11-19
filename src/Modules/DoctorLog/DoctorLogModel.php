<?php
namespace Modules\DoctorLog;

use Core\Database;
use PDO;

class DoctorLogModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function list(array $filters = []): array
    {
        $sql = 'SELECT * FROM doctor_log WHERE deleted_at IS NULL';
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
