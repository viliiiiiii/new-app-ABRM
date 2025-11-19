<?php
namespace Modules\Inventory;

use Core\Controller;
use Core\CSRF;

class InventoryController extends Controller
{
    private InventoryModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new InventoryModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $items = $this->model->list();
        $this->view->render('inventory/index', [
            'user' => $this->auth->user(),
            'items' => $items,
            'csrf' => CSRF::token(),
        ]);
    }
}
