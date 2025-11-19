<?php
namespace Core;

use Core\Database;
use PDO;
use DateTimeImmutable;

class Auth
{
    private PDO $db;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name(Config::get('app.session_name'));
            session_start();
        }
        $this->db = Database::connection();
    }

    public function attempt(string $email, string $password, string $ip): bool
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }
        if ($user['status'] !== 'active') {
            return false;
        }
        if ($this->isLockedOut($user['id'])) {
            return false;
        }
        if (password_verify($password, $user['password_hash'])) {
            $this->clearFailures($user['id']);
            $this->loginUser($user['id']);
            $this->logLogin($user['id'], $ip, $_SERVER['HTTP_USER_AGENT'] ?? 'cli');
            return true;
        }
        $this->logFailure($user['id']);
        return false;
    }

    private function loginUser(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
        }
        session_regenerate_id(true);
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    private function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    private function logFailure(int $userId): void
    {
        $stmt = $this->db->prepare('INSERT INTO login_attempts (user_id, ip_address, created_at, success) VALUES (:user_id, :ip, NOW(), 0)');
        $stmt->execute(['user_id' => $userId, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli']);
    }

    private function clearFailures(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM login_attempts WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }

    private function isLockedOut(int $userId): bool
    {
        $threshold = Config::get('app.lockout_threshold', 5);
        $minutes = Config::get('app.lockout_minutes', 15);
        $stmt = $this->db->prepare('SELECT COUNT(*) as attempts, MAX(created_at) as last_attempt FROM login_attempts WHERE user_id = :user_id AND success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)');
        $stmt->execute(['user_id' => $userId, 'minutes' => $minutes]);
        $row = $stmt->fetch();
        return $row && $row['attempts'] >= $threshold;
    }

    private function logLogin(int $userId, string $ip, string $agent): void
    {
        $stmt = $this->db->prepare('INSERT INTO login_history (user_id, ip_address, user_agent, created_at) VALUES (:user_id, :ip, :agent, NOW())');
        $stmt->execute(['user_id' => $userId, 'ip' => $ip, 'agent' => $agent]);
    }
}
