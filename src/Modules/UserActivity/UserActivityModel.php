<?php
namespace Modules\UserActivity;

use Core\Database;
use PDO;

class UserActivityModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function entries(array $filters = [], int $limit = 200): array
    {
        $sql = 'SELECT a.*, u.name AS user_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id WHERE 1=1';
        $params = [];
        if (!empty($filters['user_id'])) {
            $sql .= ' AND a.user_id = :user_id';
            $params['user_id'] = (int)$filters['user_id'];
        }
        if (!empty($filters['module'])) {
            $sql .= ' AND a.module = :module';
            $params['module'] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $sql .= ' AND a.action_type = :action';
            $params['action'] = $filters['action'];
        }
        if (!empty($filters['severity'])) {
            $sql .= ' AND a.severity = :severity';
            $params['severity'] = $filters['severity'];
        }
        if (!empty($filters['from'])) {
            $sql .= ' AND a.created_at >= :from';
            $params['from'] = $filters['from'] . ' 00:00:00';
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND a.created_at <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }
        if (!empty($filters['query'])) {
            $sql .= ' AND (a.description LIKE :query OR JSON_SEARCH(a.before_state, "one", :queryLike) IS NOT NULL OR JSON_SEARCH(a.after_state, "one", :queryLike) IS NOT NULL)';
            $params['query'] = '%' . $filters['query'] . '%';
            $params['queryLike'] = '%' . $filters['query'] . '%';
        }
        $sql .= ' ORDER BY a.created_at DESC LIMIT :limit';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $paramType);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map([$this, 'mapPayload'], $rows);
    }

    public function detail(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT a.*, u.name AS user_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id WHERE a.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapPayload($row) : null;
    }

    public function filterOptions(): array
    {
        $users = $this->db->query('SELECT id, name FROM users ORDER BY name')->fetchAll();
        $modules = $this->db->query('SELECT DISTINCT module FROM activity_log ORDER BY module')->fetchAll(PDO::FETCH_COLUMN);
        $actions = $this->db->query('SELECT DISTINCT action_type FROM activity_log ORDER BY action_type')->fetchAll(PDO::FETCH_COLUMN);
        return compact('users', 'modules', 'actions');
    }

    private function mapPayload(array $row): array
    {
        $row['before_state'] = $this->decode($row['before_state']);
        $row['after_state'] = $this->decode($row['after_state']);
        return $row;
    }

    private function decode(?string $json): ?array
    {
        if (!$json) {
            return null;
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    }
}
