<?php
namespace Modules\Notes;

use Core\Controller;
use Core\CSRF;
use Core\SavedFilters;

class NotesController extends Controller
{
    private NotesModel $model;
    private SavedFilters $filters;

    public function __construct()
    {
        parent::__construct();
        $this->model = new NotesModel($this->auth->user());
        $this->filters = new SavedFilters();
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = $this->collectFilters();
        $filters['sector_id'] = $this->auth->user()['sector_id'] ?? 0;
        $notes = $this->model->list($this->auth->user()['id'], $filters);
        $templates = $this->model->templates();
        $checklists = [];
        $readers = [];
        $comments = [];
        foreach ($notes as $note) {
            $checklists[$note['id']] = $this->model->checklist($note['id']);
            $this->model->markRead($note['id'], $this->auth->user()['id']);
            $readers[$note['id']] = $this->model->readers($note['id']);
            $comments[$note['id']] = $this->model->comments($note['id']);
        }
        $saved = $this->filters->all($this->auth->user()['id'], 'notes');
        $this->view->render('notes/index', [
            'user' => $this->auth->user(),
            'notes' => $notes,
            'checklists' => $checklists,
            'readers' => $readers,
            'comments' => $comments,
            'filters' => $filters,
            'templates' => $templates,
            'savedFilters' => $saved,
            'csrf' => CSRF::token(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $checklistItems = array_filter(array_map('trim', explode("\n", $_POST['checklist'] ?? '')));
        $noteId = $this->model->create([
            'owner_id' => $this->auth->user()['id'],
            'title' => $_POST['title'] ?? '',
            'body' => $_POST['body'] ?? '',
            'note_type' => $_POST['note_type'] ?? 'Personal',
            'reminder_at' => $_POST['reminder_at'] ?? null,
            'pinned' => !empty($_POST['pinned']) ? 1 : 0,
            'is_favourite' => !empty($_POST['is_favourite']) ? 1 : 0,
            'tags' => $_POST['tags'] ?? '',
        ], $checklistItems);
        if (!empty($_POST['share_users'])) {
            $users = array_filter(array_map('intval', explode(',', $_POST['share_users'])));
            if ($users) {
                $this->model->share($noteId, $users);
            }
        }
        header('Location: /notes');
    }

    public function toggleChecklist(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $this->model->toggleChecklist((int)$_POST['item_id'], !empty($_POST['is_done']));
        header('Location: /notes');
    }

    public function comment(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $this->model->addComment((int)$_POST['note_id'], $this->auth->user()['id'], $_POST['comment']);
        header('Location: /notes');
    }

    public function share(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $users = array_filter(array_map('intval', explode(',', $_POST['share_users'] ?? '')));
        $sectors = array_filter(array_map('intval', explode(',', $_POST['share_sectors'] ?? '')));
        $this->model->share((int)$_POST['note_id'], $users, $sectors);
        header('Location: /notes');
    }

    public function pin(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $this->model->pin((int)$_POST['note_id'], !empty($_POST['pinned']));
        header('Location: /notes');
    }

    public function favourite(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $this->model->favourite((int)$_POST['note_id'], !empty($_POST['fav']));
        header('Location: /notes');
    }

    public function saveFilter(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $this->filters->save($this->auth->user()['id'], 'notes', trim($_POST['filter_name']), $this->collectFiltersFromPost());
        header('Location: /notes');
    }

    private function collectFilters(): array
    {
        return [
            'type' => $_GET['type'] ?? null,
            'tag' => $_GET['tag'] ?? null,
            'pinned' => $_GET['pinned'] ?? null,
            'favourite' => $_GET['favourite'] ?? null,
            'incomplete' => $_GET['incomplete'] ?? null,
        ];
    }

    private function collectFiltersFromPost(): array
    {
        return [
            'type' => $_POST['type'] ?? null,
            'tag' => $_POST['tag'] ?? null,
            'pinned' => $_POST['pinned'] ?? null,
            'favourite' => $_POST['favourite'] ?? null,
            'incomplete' => $_POST['incomplete'] ?? null,
        ];
    }

    private function guardCsrf(): void
    {
        if (!CSRF::validate($_POST['_token'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF token');
        }
    }
}
