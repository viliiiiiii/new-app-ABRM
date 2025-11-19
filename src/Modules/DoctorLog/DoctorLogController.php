<?php
namespace Modules\DoctorLog;

use Core\Controller;
use Core\CSRF;

class DoctorLogController extends Controller
{
    private DoctorLogModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new DoctorLogModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $entries = $this->model->list();
        $this->view->render('doctor_log/index', [
            'user' => $this->auth->user(),
            'entries' => $entries,
            'csrf' => CSRF::token(),
        ]);
    }
}
