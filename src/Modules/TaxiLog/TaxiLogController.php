<?php
namespace Modules\TaxiLog;

use Core\Controller;
use Core\CSRF;

class TaxiLogController extends Controller
{
    private TaxiLogModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TaxiLogModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $entries = $this->model->latest();
        $this->view->render('taxi_log/index', [
            'user' => $this->auth->user(),
            'entries' => $entries,
            'csrf' => CSRF::token(),
        ]);
    }
}
