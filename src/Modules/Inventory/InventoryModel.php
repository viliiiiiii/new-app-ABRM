<?php
namespace Modules\Inventory;

use Core\ActivityLogger;
use Core\Database;
use Core\Notifications;
use PDO;

class InventoryModel
{
    private PDO $db;
    private ActivityLogger $logger;
    private Notifications $notifications;

    public function __construct(?array $user = null)
    {
        $this->db = Database::connection();
        $this->logger = new ActivityLogger($user);
        $this->notifications = new Notifications();
    }

    public function list(array $filters = []): array
    {
        $sql = 'SELECT * FROM inventory_items WHERE deleted_at IS NULL';
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND category = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['location'])) {
            $sql .= ' AND location = :location';
            $params['location'] = $filters['location'];
        }
        if (!empty($filters['query'])) {
            $sql .= ' AND (name LIKE :query OR sku LIKE :query)';
            $params['query'] = '%' . $filters['query'] . '%';
        }
        $sql .= ' ORDER BY name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function alerts(): array
    {
        $stmt = $this->db->query('SELECT id, name, quantity_on_hand, min_stock, max_stock FROM inventory_items WHERE deleted_at IS NULL');
        $alerts = ['low' => [], 'over' => []];
        foreach ($stmt->fetchAll() as $row) {
            if ($row['quantity_on_hand'] < $row['min_stock']) {
                $alerts['low'][] = $row;
            }
            if ($row['max_stock'] > 0 && $row['quantity_on_hand'] > $row['max_stock']) {
                $alerts['over'][] = $row;
            }
        }
        return $alerts;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO inventory_items (sku, name, category, location, quantity_on_hand, min_stock, max_stock, condition, status, notes, created_by, updated_by) VALUES (:sku, :name, :category, :location, :qty, :min, :max, :condition, :status, :notes, :user, :user)');
        $stmt->execute([
            'sku' => $data['sku'],
            'name' => $data['name'],
            'category' => $data['category'],
            'location' => $data['location'],
            'qty' => $data['quantity_on_hand'],
            'min' => $data['min_stock'],
            'max' => $data['max_stock'],
            'condition' => $data['condition'],
            'status' => $data['status'],
            'notes' => $data['notes'],
            'user' => $data['user_id'],
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->logger->log('inventory', 'create', $id, null, $data, 'Inventory item created');
        return $id;
    }

    public function logMovement(array $data): void
    {
        $item = $this->find($data['item_id']);
        if (!$item) {
            return;
        }
        $stmt = $this->db->prepare('INSERT INTO inventory_movements (item_id, movement_time, from_location, to_location, quantity_moved, moved_by, reason, issued_signature_path, received_signature_path, notes) VALUES (:item_id, :time, :from_location, :to_location, :quantity, :user, :reason, :issued, :received, :notes)');
        $stmt->execute([
            'item_id' => $data['item_id'],
            'time' => $data['movement_time'],
            'from_location' => $data['from_location'],
            'to_location' => $data['to_location'],
            'quantity' => $data['quantity_moved'],
            'user' => $data['user_id'],
            'reason' => $data['reason'],
            'issued' => $data['issued_signature'] ?? null,
            'received' => $data['received_signature'] ?? null,
            'notes' => $data['notes'],
        ]);
        $newQty = max(0, $item['quantity_on_hand'] + (int)$data['quantity_moved']);
        $stmt = $this->db->prepare('UPDATE inventory_items SET quantity_on_hand = :qty, updated_by = :user WHERE id = :id');
        $stmt->execute(['qty' => $newQty, 'user' => $data['user_id'], 'id' => $data['item_id']]);
        $change = (int)$data['quantity_moved'];
        $threshold = max(10, (int)ceil(abs($item['quantity_on_hand'] ?? 0) * 0.5));
        $isScrap = isset($data['reason']) && stripos($data['reason'], 'scrap') !== false;
        $options = [];
        if ($isScrap || abs($change) >= $threshold) {
            $options = [
                'severity' => $isScrap ? 'critical' : 'warning',
                'alert' => true,
                'alert_message' => 'Inventory alert: ' . ($item['name'] ?? ('Item #' . $data['item_id'])) . ' adjusted by ' . $change,
                'alert_link' => '/inventory',
            ];
        }
        $this->logger->log('inventory', 'movement', $data['item_id'], $item, ['quantity_on_hand' => $newQty], 'Movement recorded', $options);
        $alerts = $this->alerts();
        foreach ($alerts['low'] as $alert) {
            if ($alert['id'] == $data['item_id']) {
                $this->notifications->create($data['user_id'], 'Low stock: ' . $item['name'], '/inventory', 'warning');
            }
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM inventory_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function movements(int $itemId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM inventory_movements WHERE item_id = :id ORDER BY movement_time DESC LIMIT 20');
        $stmt->execute(['id' => $itemId]);
        return $stmt->fetchAll();
    }

    public function startStocktake(string $name, string $location, string $date, int $userId): int
    {
        $stmt = $this->db->prepare('INSERT INTO stocktake_sessions (name, location, session_date, created_by) VALUES (:name, :location, :session_date, :user)');
        $stmt->execute(['name' => $name, 'location' => $location, 'session_date' => $date, 'user' => $userId]);
        $sessionId = (int)$this->db->lastInsertId();
        $items = $this->list();
        foreach ($items as $item) {
            $insert = $this->db->prepare('INSERT INTO stocktake_items (session_id, item_id, expected_quantity, counted_quantity, variance) VALUES (:session, :item, :expected, :counted, 0)');
            $insert->execute([
                'session' => $sessionId,
                'item' => $item['id'],
                'expected' => $item['quantity_on_hand'],
                'counted' => $item['quantity_on_hand'],
            ]);
        }
        return $sessionId;
    }

    public function stocktakeSessions(): array
    {
        $stmt = $this->db->query('SELECT * FROM stocktake_sessions ORDER BY session_date DESC LIMIT 5');
        return $stmt->fetchAll();
    }

    public function stocktakeItems(int $sessionId): array
    {
        $stmt = $this->db->prepare('SELECT si.*, ii.name FROM stocktake_items si JOIN inventory_items ii ON ii.id = si.item_id WHERE si.session_id = :session');
        $stmt->execute(['session' => $sessionId]);
        return $stmt->fetchAll();
    }

    public function updateStocktakeItem(int $itemId, int $counted): void
    {
        $stmt = $this->db->prepare('UPDATE stocktake_items SET counted_quantity = :counted, variance = :counted - expected_quantity WHERE id = :id');
        $stmt->execute(['counted' => $counted, 'id' => $itemId]);
    }
}
