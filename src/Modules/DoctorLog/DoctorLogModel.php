<?php
namespace Modules\DoctorLog;

use Core\ActivityLogger;
use Core\Database;
use PDO;

class DoctorLogModel
{
    private PDO $db;
    private ActivityLogger $logger;

    public function __construct(?array $user = null)
    {
        $this->db = Database::connection();
        $this->logger = new ActivityLogger($user);
    }

    public function list(array $filters = []): array
    {
        $sql = 'SELECT * FROM doctor_log WHERE deleted_at IS NULL';
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['room'])) {
            $sql .= ' AND room_number = :room';
            $params['room'] = $filters['room'];
        }
        if (!empty($filters['doctor'])) {
            $sql .= ' AND doctor_name LIKE :doctor';
            $params['doctor'] = '%' . $filters['doctor'] . '%';
        }
        if (!empty($filters['from'])) {
            $sql .= ' AND time_called >= :from';
            $params['from'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND time_called <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }
        $sql .= ' ORDER BY time_called DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO doctor_log (room_number, time_called, time_arrived, doctor_name, reason, status, created_by) VALUES (:room, :called, :arrived, :doctor, :reason, :status, :user)');
        $stmt->execute([
            'room' => $data['room_number'],
            'called' => $data['time_called'],
            'arrived' => $data['time_arrived'],
            'doctor' => $data['doctor_name'],
            'reason' => $data['reason'],
            'status' => $data['status'],
            'user' => $data['user_id'],
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->logger->log('doctor_log', 'create', $id, null, $data, 'Doctor log entry');
        return $id;
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE doctor_log SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $this->logger->log('doctor_log', 'soft_delete', $id);
    }
}
