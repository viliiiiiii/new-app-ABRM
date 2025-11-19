<?php
namespace Modules\Notes;

use Core\Controller;
use Core\CSRF;

class NotesController extends Controller
{
    private NotesModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new NotesModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $notes = $this->model->list($this->auth->user()['id']);
        $this->view->render('notes/index', [
            'user' => $this->auth->user(),
            'notes' => $notes,
            'csrf' => CSRF::token(),
        ]);
    }
}
