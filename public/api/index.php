<?php
require_once __DIR__ . '/../../bootstrap.php';

use Core\Auth;
use Core\CSRF;
use Core\Database;
use Core\GlobalSearch;
use Core\Notifications;
use Modules\LostAndFound\LostAndFoundModel;
use Modules\Inventory\InventoryModel;
use Modules\TaxiLog\TaxiLogModel;
use Modules\DoctorLog\DoctorLogModel;
use Modules\Notes\NotesModel;

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthenticated']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';
$rawInput = '';

if ($method !== 'GET') {
    $rawInput = file_get_contents('php://input');
    $json = $rawInput ? json_decode($rawInput, true) : [];
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? ($json['token'] ?? '');
    if (!CSRF::validate($token)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}

try {
    switch ($resource) {
        case 'lost-and-found':
            $model = new LostAndFoundModel($auth->user());
            $filters = [
                'query' => $_GET['query'] ?? $_GET['q'] ?? null,
                'state' => $_GET['state'] ?? null,
            ];
            echo json_encode(['success' => true, 'data' => $model->all($filters)]);
            break;
        case 'inventory':
            $model = new InventoryModel($auth->user());
            echo json_encode(['success' => true, 'data' => $model->list($_GET)]);
            break;
        case 'taxi-log':
            $model = new TaxiLogModel($auth->user());
            echo json_encode(['success' => true, 'data' => $model->latest($_GET)]);
            break;
        case 'doctor-log':
            $model = new DoctorLogModel($auth->user());
            echo json_encode(['success' => true, 'data' => $model->list($_GET)]);
            break;
        case 'notes':
            $model = new NotesModel($auth->user());
            $params = $_GET;
            $params['sector_id'] = $auth->user()['sector_id'] ?? 0;
            echo json_encode(['success' => true, 'data' => $model->list($auth->user()['id'], $params)]);
            break;
        case 'search':
            $search = new GlobalSearch();
            $results = $search->search($_GET['q'] ?? '', $_GET['module'] ?? null);
            echo json_encode(['success' => true, 'data' => $results]);
            break;
        case 'notifications':
            $notifications = new Notifications();
            echo json_encode([
                'success' => true,
                'data' => $notifications->latest($auth->user()['id']),
                'meta' => ['unread' => $notifications->unreadCount($auth->user()['id'])],
            ]);
            break;
        case 'user-settings':
            if ($method !== 'POST') {
                throw new RuntimeException('Unsupported method');
            }
            $payload = $rawInput ? json_decode($rawInput, true) : [];
            $theme = $payload['theme'] ?? 'light';
            $stmt = Database::connection()->prepare('UPDATE users SET theme_preference = :theme WHERE id = :id');
            $stmt->execute(['theme' => $theme, 'id' => $auth->user()['id']]);
            echo json_encode(['success' => true]);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Unknown resource']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
