<?php
namespace Modules\TaxiLog;

use Core\ActivityLogger;
use Core\Database;
use PDO;

class TaxiLogModel
{
    private PDO $db;
    private ActivityLogger $logger;

    public function __construct(?array $user = null)
    {
        $this->db = Database::connection();
        $this->logger = new ActivityLogger($user);
    }

    public function latest(array $filters = []): array
    {
        $sql = 'SELECT * FROM taxi_log WHERE deleted_at IS NULL';
        $params = [];
        if (!empty($filters['date_from'])) {
            $sql .= ' AND ride_time >= :from';
            $params['from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND ride_time <= :to';
            $params['to'] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['driver'])) {
            $sql .= ' AND driver_name = :driver';
            $params['driver'] = $filters['driver'];
        }
        if (!empty($filters['guest'])) {
            $sql .= ' AND guest_name LIKE :guest';
            $params['guest'] = '%' . $filters['guest'] . '%';
        }
        if (!empty($filters['room'])) {
            $sql .= ' AND room_number = :room';
            $params['room'] = $filters['room'];
        }
        $sql .= ' ORDER BY ride_time DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO taxi_log (ride_time, start_location, destination, guest_name, room_number, driver_name, price, notes, created_by) VALUES (:ride_time, :start_location, :destination, :guest_name, :room_number, :driver_name, :price, :notes, :created_by)');
        $stmt->execute([
            'ride_time' => $data['ride_time'],
            'start_location' => $data['start_location'],
            'destination' => $data['destination'],
            'guest_name' => $data['guest_name'],
            'room_number' => $data['room_number'],
            'driver_name' => $data['driver_name'],
            'price' => $data['price'],
            'notes' => $data['notes'],
            'created_by' => $data['created_by'],
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->logger->log('taxi_log', 'create', $id, null, $data, 'Taxi log entry added');
        return $id;
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE taxi_log SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $this->logger->log('taxi_log', 'soft_delete', $id);
    }

    public function monthlySummary(string $month): array
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS rides, COALESCE(SUM(price),0) AS revenue FROM taxi_log WHERE deleted_at IS NULL AND DATE_FORMAT(ride_time, "%Y-%m") = :month');
        $stmt->execute(['month' => $month]);
        return $stmt->fetch() ?: ['rides' => 0, 'revenue' => 0];
    }

    public function frequentGuests(string $from, string $to): array
    {
        $stmt = $this->db->prepare('SELECT guest_name, room_number, COUNT(*) AS total FROM taxi_log WHERE deleted_at IS NULL AND ride_time BETWEEN :from AND :to GROUP BY guest_name, room_number ORDER BY total DESC LIMIT 5');
        $stmt->execute([
            'from' => $from,
            'to' => $to,
        ]);
        return $stmt->fetchAll();
    }
}
