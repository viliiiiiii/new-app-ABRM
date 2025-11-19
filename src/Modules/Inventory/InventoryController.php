<?php
namespace Modules\Inventory;

use Core\Controller;
use Core\CSRF;
use Core\ExportExcel;
use Core\ExportPdf;
use Core\SavedFilters;

class InventoryController extends Controller
{
    private InventoryModel $model;
    private SavedFilters $filters;

    public function __construct()
    {
        parent::__construct();
        $this->model = new InventoryModel($this->auth->user());
        $this->filters = new SavedFilters();
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = $this->collectFilters();
        $items = $this->model->list($filters);
        $alerts = $this->model->alerts();
        $saved = $this->filters->all($this->auth->user()['id'], 'inventory');
        $sessions = $this->model->stocktakeSessions();
        $sessionItems = null;
        if (!empty($_GET['session'])) {
            $sessionItems = $this->model->stocktakeItems((int)$_GET['session']);
        }
        $this->view->render('inventory/index', [
            'user' => $this->auth->user(),
            'items' => $items,
            'alerts' => $alerts,
            'filters' => $filters,
            'savedFilters' => $saved,
            'sessions' => $sessions,
            'sessionItems' => $sessionItems,
            'csrf' => CSRF::token(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePermission('inventory.manage');
        $this->guardCsrf();
        $data = [
            'sku' => $_POST['sku'] ?? '',
            'name' => $_POST['name'],
            'category' => $_POST['category'] ?? '',
            'location' => $_POST['location'] ?? '',
            'quantity_on_hand' => (int)($_POST['quantity_on_hand'] ?? 0),
            'min_stock' => (int)($_POST['min_stock'] ?? 0),
            'max_stock' => (int)($_POST['max_stock'] ?? 0),
            'condition' => $_POST['condition'] ?? 'new',
            'status' => $_POST['status'] ?? 'active',
            'notes' => $_POST['notes'] ?? '',
            'user_id' => $this->auth->user()['id'],
        ];
        $this->model->create($data);
        header('Location: /inventory');
    }

    public function movement(): void
    {
        $this->requireAuth();
        $this->requirePermission('inventory.manage');
        $this->guardCsrf();
        $data = [
            'item_id' => (int)$_POST['item_id'],
            'movement_time' => $this->formatDateTime($_POST['movement_time'] ?? ''),
            'from_location' => $_POST['from_location'] ?? '',
            'to_location' => $_POST['to_location'] ?? '',
            'quantity_moved' => (int)$_POST['quantity_moved'],
            'reason' => $_POST['reason'] ?? '',
            'issued_signature' => $_POST['issued_signature'] ?? null,
            'received_signature' => $_POST['received_signature'] ?? null,
            'notes' => $_POST['notes'] ?? '',
            'user_id' => $this->auth->user()['id'],
        ];
        $this->model->logMovement($data);
        header('Location: /inventory');
    }

    public function startStocktake(): void
    {
        $this->requireAuth();
        $this->requirePermission('inventory.manage');
        $this->guardCsrf();
        $sessionId = $this->model->startStocktake($_POST['name'], $_POST['location'], $_POST['session_date'], $this->auth->user()['id']);
        header('Location: /inventory?session=' . $sessionId);
    }

    public function updateStocktake(): void
    {
        $this->requireAuth();
        $this->requirePermission('inventory.manage');
        $this->guardCsrf();
        if (!empty($_POST['counts']) && is_array($_POST['counts'])) {
            foreach ($_POST['counts'] as $itemId => $counted) {
                $this->model->updateStocktakeItem((int)$itemId, (int)$counted);
            }
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/inventory'));
    }

    public function saveFilter(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $this->filters->save($this->auth->user()['id'], 'inventory', trim($_POST['filter_name']), $this->collectFiltersFromPost());
        header('Location: /inventory');
    }

    public function export(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $filters = $this->collectFiltersFromPost();
        $items = $this->model->list($filters);
        if (($_POST['format'] ?? 'pdf') === 'excel') {
            ExportExcel::fromArray($items, 'inventory.xlsx');
            return;
        }
        $html = '<h1>Inventory</h1><table border="1"><tr><th>SKU</th><th>Name</th><th>Qty</th><th>Location</th></tr>';
        foreach ($items as $item) {
            $html .= '<tr><td>' . htmlspecialchars($item['sku']) . '</td><td>' . htmlspecialchars($item['name']) . '</td><td>' . htmlspecialchars($item['quantity_on_hand']) . '</td><td>' . htmlspecialchars($item['location']) . '</td></tr>';
        }
        $html .= '</table>';
        ExportPdf::fromHtml($html, 'inventory.pdf');
    }

    private function collectFilters(): array
    {
        return [
            'query' => $_GET['query'] ?? $_GET['q'] ?? null,
            'status' => $_GET['status'] ?? null,
            'category' => $_GET['category'] ?? null,
            'location' => $_GET['location'] ?? null,
        ];
    }

    private function collectFiltersFromPost(): array
    {
        return [
            'query' => $_POST['query'] ?? $_POST['q'] ?? null,
            'status' => $_POST['status'] ?? null,
            'category' => $_POST['category'] ?? null,
            'location' => $_POST['location'] ?? null,
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
