<?php
namespace Modules\TaxiLog;

use Core\Database;
use PDO;

class TaxiLogModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
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
            $params['to'] = $filters['date_to'];
        }
        $sql .= ' ORDER BY ride_time DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
