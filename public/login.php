<?php
require_once __DIR__ . '/../bootstrap.php';

use Core\Auth;
use Core\CSRF;

$auth = new Auth();
if ($auth->check()) {
    header('Location: /');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        if ($auth->attempt($email, $password, $_SERVER['REMOTE_ADDR'] ?? 'cli')) {
            header('Location: /');
            exit;
        }
        $error = 'Invalid credentials or locked account.';
    }
}
$token = CSRF::token();
include __DIR__ . '/../templates/landing/login.php';
