<?php
namespace Modules\LostAndFound;

use Core\ActivityLogger;
use Core\Controller;
use Core\CSRF;
use Core\ExportExcel;
use Core\ExportPdf;
use Core\SavedFilters;

class LostAndFoundController extends Controller
{
    private LostAndFoundModel $model;
    private SavedFilters $savedFilters;
    private ActivityLogger $logger;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LostAndFoundModel($this->auth->user());
        $this->savedFilters = new SavedFilters();
        $this->logger = new ActivityLogger($this->auth->user());
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = $this->collectFilters();
        $items = $this->model->all($filters);
        $savedFilters = $this->savedFilters->all($this->auth->user()['id'], 'lost-and-found');
        $historyItem = isset($_GET['history']) ? (int)$_GET['history'] : null;
        $history = $historyItem ? $this->model->history($historyItem) : null;
        $stats = $this->model->stats();
        $reminders = $this->model->dueReminders();
        $this->view->render('lost_and_found/index', [
            'user' => $this->auth->user(),
            'items' => $items,
            'filters' => $filters,
            'savedFilters' => $savedFilters,
            'history' => $history,
            'historyItem' => $historyItem,
            'reminders' => $reminders,
            'stats' => $stats,
            'csrf' => CSRF::token(),
        ]);
    }

    public function recycleBin(): void
    {
        $this->requireAuth();
        $this->requirePermission('lostandfound.manage');
        $items = $this->model->all([], true);
        $this->view->render('lost_and_found/recycle_bin', [
            'user' => $this->auth->user(),
            'items' => $items,
            'csrf' => CSRF::token(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePermission('lostandfound.create');
        $this->guardCsrf();
        $data = [
            'item_code' => bin2hex(random_bytes(6)),
            'item_name' => trim($_POST['item_name']),
            'category' => $_POST['category'] ?? null,
            'tags' => $_POST['tags'] ?? null,
            'lifecycle_state' => $_POST['lifecycle_state'] ?? 'new',
            'status' => $_POST['status'] ?? 'open',
            'location_area' => $_POST['location_area'] ?? null,
            'location_building' => $_POST['location_building'] ?? null,
            'location_floor' => $_POST['location_floor'] ?? null,
            'location_exact' => $_POST['location_exact'] ?? null,
            'owner_name' => $_POST['owner_name'] ?? null,
            'owner_contact' => $_POST['owner_contact'] ?? null,
            'owner_status' => $_POST['owner_status'] ?? 'unknown',
            'reminder_date' => $_POST['reminder_date'] ?? null,
            'retention_date' => $_POST['retention_date'] ?? null,
            'high_value' => !empty($_POST['high_value']),
            'sensitive_document' => !empty($_POST['sensitive_document']),
            'description' => $_POST['description'] ?? null,
            'notes' => $_POST['notes'] ?? null,
            'found_at' => $this->formatDateTime($_POST['found_at'] ?? ''),
            'created_by' => $this->auth->user()['id'],
        ];
        $this->model->create($data);
        header('Location: /lost-and-found');
    }

    public function updateText(): void
    {
        $this->requireAuth();
        $this->requirePermission('lostandfound.manage');
        $this->guardCsrf();
        $id = (int)$_POST['item_id'];
        $fields = [
            'description' => $_POST['description'] ?? '',
            'notes' => $_POST['notes'] ?? '',
        ];
        $this->model->updateTextFields($id, $fields, $this->auth->user()['id']);
        header('Location: /lost-and-found?history=' . $id);
    }

    public function changeState(): void
    {
        $this->requireAuth();
        $this->requirePermission('lostandfound.manage');
        $this->guardCsrf();
        $ids = array_filter(array_map('intval', explode(',', $_POST['item_ids'] ?? '')));
        $state = $_POST['state'];
        $notes = $_POST['state_notes'] ?? '';
        foreach ($ids as $id) {
            $this->model->changeState($id, $state, $this->auth->user()['id'], $notes);
        }
        header('Location: /lost-and-found');
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->requirePermission('lostandfound.manage');
        $this->guardCsrf();
        $id = (int)$_POST['item_id'];
        $this->model->softDelete($id, $this->auth->user()['id']);
        header('Location: /lost-and-found');
    }

    public function restore(): void
    {
        $this->requireAuth();
        $this->requirePermission('lostandfound.manage');
        $this->guardCsrf();
        $id = (int)$_POST['item_id'];
        $this->model->restore($id, $this->auth->user()['id']);
        header('Location: /lost-and-found/recycle-bin');
    }

    public function release(): void
    {
        $this->requireAuth();
        $this->requirePermission('lostandfound.manage');
        $this->guardCsrf();
        $data = [
            'recipient_name' => $_POST['recipient_name'],
            'recipient_id' => $_POST['recipient_id'] ?? null,
            'recipient_contact' => $_POST['recipient_contact'] ?? null,
            'staff_name' => $_POST['staff_name'] ?? $this->auth->user()['name'],
            'staff_signature_path' => $_POST['staff_signature'] ?? null,
            'recipient_signature_path' => $_POST['recipient_signature'] ?? null,
            'user_id' => $this->auth->user()['id'],
        ];
        $this->model->release((int)$_POST['item_id'], $data);
        header('Location: /lost-and-found');
    }

    public function saveFilter(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $name = trim($_POST['filter_name']);
        $filters = $this->collectFiltersFromPost();
        $this->savedFilters->save($this->auth->user()['id'], 'lost-and-found', $name, $filters);
        header('Location: /lost-and-found');
    }

    public function export(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $format = $_POST['format'] ?? 'pdf';
        $filters = $this->collectFiltersFromPost();
        $items = $this->model->all($filters);
        $html = '<h1>Lost and Found export</h1><table border="1"><tr><th>Code</th><th>Name</th><th>State</th><th>Location</th></tr>';
        foreach ($items as $item) {
            $html .= '<tr><td>' . htmlspecialchars($item['item_code']) . '</td><td>' . htmlspecialchars($item['item_name']) . '</td><td>' . htmlspecialchars($item['lifecycle_state']) . '</td><td>' . htmlspecialchars($item['location_area'] . ' ' . $item['location_exact']) . '</td></tr>';
        }
        $html .= '</table>';
        if ($format === 'excel') {
            ExportExcel::fromArray($items, 'lost_and_found.xlsx');
            return;
        }
        ExportPdf::fromHtml($html, 'lost_and_found.pdf');
    }

    private function collectFilters(): array
    {
        return [
            'query' => $_GET['query'] ?? $_GET['q'] ?? null,
            'state' => $_GET['state'] ?? null,
            'category' => $_GET['category'] ?? null,
            'tag' => $_GET['tag'] ?? null,
            'from' => $_GET['from'] ?? null,
            'to' => $_GET['to'] ?? null,
            'high_value' => $_GET['high_value'] ?? null,
        ];
    }

    private function collectFiltersFromPost(): array
    {
        return [
            'query' => $_POST['query'] ?? $_POST['q'] ?? null,
            'state' => $_POST['state'] ?? null,
            'category' => $_POST['category'] ?? null,
            'tag' => $_POST['tag'] ?? null,
            'from' => $_POST['from'] ?? null,
            'to' => $_POST['to'] ?? null,
            'high_value' => $_POST['high_value'] ?? null,
        ];
    }

    private function guardCsrf(): void
    {
        if (!CSRF::validate($_POST['_token'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF token');
        }
    }

    private function formatDateTime(?string $value): string
    {
        if (!$value) {
            return date('Y-m-d H:i:s');
        }
        return str_replace('T', ' ', $value);
    }
}
