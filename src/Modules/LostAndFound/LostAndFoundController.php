<?php
namespace Modules\LostAndFound;

use Core\Controller;
use Core\CSRF;

class LostAndFoundController extends Controller
{
    private LostAndFoundModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LostAndFoundModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $items = $this->model->all([
            'status' => $_GET['status'] ?? null,
            'query' => $_GET['q'] ?? null,
        ]);
        $this->view->render('lost_and_found/index', [
            'user' => $this->auth->user(),
            'items' => $items,
            'csrf' => CSRF::token(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePermission('lostandfound.create');
        if (!CSRF::validate($_POST['_token'] ?? '')) {
            http_response_code(400);
            exit('Invalid token');
        }
        $id = $this->model->create([
            'item_name' => trim($_POST['item_name']),
            'category' => $_POST['category'] ?? 'general',
            'status' => $_POST['status'] ?? 'new',
            'location' => $_POST['location'] ?? '',
            'description' => $_POST['description'] ?? '',
            'found_at' => $_POST['found_at'] ?? date('Y-m-d'),
            'created_by' => $this->auth->user()['id'],
        ]);
        header('Location: /lost-and-found');
    }
}
