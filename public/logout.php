<?php
require_once __DIR__ . '/../bootstrap.php';

use Core\Auth;

$auth = new Auth();
$auth->logout();
header('Location: /login');
