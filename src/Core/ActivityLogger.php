<?php
namespace Core;

use Core\Database;
use Core\Notifications;
use PDO;

class ActivityLogger
{
    private PDO $db;
    private ?array $user;
    private Notifications $notifications;
    private ?array $privilegedUsers = null;

    public function __construct(?array $user)
    {
        $this->db = Database::connection();
        $this->user = $user;
        $this->notifications = new Notifications();
    }

    public function log(string $module, string $action, int $recordId, array $before = null, array $after = null, string $description = '', array $options = []): void
    {
        $severity = $options['severity'] ?? 'info';
        $alertFlag = !empty($options['alert']);
        $alertMessage = $options['alert_message'] ?? $description;
        $stmt = $this->db->prepare('INSERT INTO activity_log (user_id, action_type, module, record_id, description, before_state, after_state, ip, severity, alert_flag, alert_message) VALUES (:user_id, :action, :module, :record, :description, :before_state, :after_state, :ip, :severity, :alert_flag, :alert_message)');
        $stmt->execute([
            'user_id' => $this->user['id'] ?? null,
            'action' => $action,
            'module' => $module,
            'record' => $recordId,
            'description' => $description,
            'before_state' => $before ? json_encode($before, JSON_PRETTY_PRINT) : null,
            'after_state' => $after ? json_encode($after, JSON_PRETTY_PRINT) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'severity' => $severity,
            'alert_flag' => $alertFlag ? 1 : 0,
            'alert_message' => $alertFlag ? $alertMessage : null,
        ]);
        if ($alertFlag && $alertMessage) {
            $this->dispatchAlert($alertMessage, $options['alert_link'] ?? '/activity', $severity);
        }
    }

    private function dispatchAlert(string $message, string $link, string $severity): void
    {
        $type = $severity === 'critical' ? 'error' : 'warning';
        foreach ($this->privilegedUserIds() as $userId) {
            $this->notifications->create((int)$userId, $message, $link, $type);
        }
    }

    private function privilegedUserIds(): array
    {
        if ($this->privilegedUsers !== null) {
            return $this->privilegedUsers;
        }
        $stmt = $this->db->query("SELECT id FROM users WHERE role_key IN ('app_owner','admin')");
        $this->privilegedUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $this->privilegedUsers;
    }
}
