<?php
namespace Core;

use Core\Database;
use PDO;

class GlobalSearch
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function search(string $term, ?string $module = null): array
    {
        $term = '%' . $term . '%';
        $results = [];
        if (!$module || $module === 'lost-and-found') {
            $stmt = $this->db->prepare('SELECT id, item_name AS title, description AS snippet, "lost-and-found" AS module FROM lost_and_found_items WHERE deleted_at IS NULL AND (item_name LIKE :term OR description LIKE :term) LIMIT 10');
            $stmt->execute(['term' => $term]);
            $results = array_merge($results, $stmt->fetchAll());
        }
        if (!$module || $module === 'inventory') {
            $stmt = $this->db->prepare('SELECT id, name AS title, notes AS snippet, "inventory" AS module FROM inventory_items WHERE deleted_at IS NULL AND (name LIKE :term OR notes LIKE :term) LIMIT 10');
            $stmt->execute(['term' => $term]);
            $results = array_merge($results, $stmt->fetchAll());
        }
        if (!$module || $module === 'taxi-log') {
            $stmt = $this->db->prepare('SELECT id, CONCAT(start_location, " → ", destination) AS title, notes AS snippet, "taxi-log" AS module FROM taxi_log WHERE deleted_at IS NULL AND (start_location LIKE :term OR destination LIKE :term OR guest_name LIKE :term) LIMIT 10');
            $stmt->execute(['term' => $term]);
            $results = array_merge($results, $stmt->fetchAll());
        }
        if (!$module || $module === 'doctor-log') {
            $stmt = $this->db->prepare('SELECT id, CONCAT("Room ", room_number) AS title, reason AS snippet, "doctor-log" AS module FROM doctor_log WHERE deleted_at IS NULL AND (room_number LIKE :term OR reason LIKE :term) LIMIT 10');
            $stmt->execute(['term' => $term]);
            $results = array_merge($results, $stmt->fetchAll());
        }
        if (!$module || $module === 'notes') {
            $stmt = $this->db->prepare('SELECT id, title AS title, body AS snippet, "notes" AS module FROM notes WHERE deleted_at IS NULL AND (title LIKE :term OR body LIKE :term) LIMIT 10');
            $stmt->execute(['term' => $term]);
            $results = array_merge($results, $stmt->fetchAll());
        }
        return $results;
    }
}
