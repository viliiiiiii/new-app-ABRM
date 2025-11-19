<?php
require_once __DIR__ . '/../../bootstrap.php';

use Core\Auth;
use Core\CSRF;
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

switch ($resource) {
    case 'lost-and-found':
        $model = new LostAndFoundModel();
        echo json_encode(['success' => true, 'data' => $model->all(['query' => $_GET['q'] ?? null])]);
        break;
    case 'inventory':
        $model = new InventoryModel();
        echo json_encode(['success' => true, 'data' => $model->list()]);
        break;
    case 'taxi-log':
        $model = new TaxiLogModel();
        echo json_encode(['success' => true, 'data' => $model->latest()]);
        break;
    case 'doctor-log':
        $model = new DoctorLogModel();
        echo json_encode(['success' => true, 'data' => $model->list()]);
        break;
    case 'notes':
        $model = new NotesModel();
        echo json_encode(['success' => true, 'data' => $model->list($auth->user()['id'])]);
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Unknown resource']);
}
