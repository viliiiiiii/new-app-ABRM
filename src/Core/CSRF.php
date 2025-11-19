<?php
namespace Core;

class CSRF
{
    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $name = Config::get('app.csrf_token_name');
        if (empty($_SESSION[$name])) {
            $_SESSION[$name] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$name];
    }

    public static function validate(?string $token): bool
    {
        $name = Config::get('app.csrf_token_name');
        return hash_equals($_SESSION[$name] ?? '', $token ?? '');
    }
}
