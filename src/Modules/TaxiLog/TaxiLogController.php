<?php
namespace Modules\TaxiLog;

use Core\Controller;
use Core\CSRF;
use Core\ExportExcel;
use Core\ExportPdf;
use Core\SavedFilters;

class TaxiLogController extends Controller
{
    private TaxiLogModel $model;
    private SavedFilters $filters;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TaxiLogModel($this->auth->user());
        $this->filters = new SavedFilters();
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = $this->collectFilters();
        $entries = $this->model->latest($filters);
        $saved = $this->filters->all($this->auth->user()['id'], 'taxi-log');
        $summary = $this->model->monthlySummary($filters['month'] ?? date('Y-m'));
        $frequent = $this->model->frequentGuests($filters['date_from'] ?? date('Y-m-01'), $filters['date_to'] ?? date('Y-m-t'));
        $this->view->render('taxi_log/index', [
            'user' => $this->auth->user(),
            'entries' => $entries,
            'filters' => $filters,
            'savedFilters' => $saved,
            'summary' => $summary,
            'frequent' => $frequent,
            'csrf' => CSRF::token(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePermission('taxilog.manage');
        $this->guardCsrf();
        $data = [
            'ride_time' => $this->formatDateTime($_POST['ride_time'] ?? ''),
            'start_location' => $_POST['start_location'],
            'destination' => $_POST['destination'],
            'guest_name' => $_POST['guest_name'] ?? '',
            'room_number' => $_POST['room_number'] ?? '',
            'driver_name' => $_POST['driver_name'] ?? '',
            'price' => (float)($_POST['price'] ?? 0),
            'notes' => $_POST['notes'] ?? '',
            'created_by' => $this->auth->user()['id'],
        ];
        $this->model->create($data);
        header('Location: /taxi-log');
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->requirePermission('taxilog.manage');
        $this->guardCsrf();
        $this->model->softDelete((int)$_POST['entry_id']);
        header('Location: /taxi-log');
    }

    public function saveFilter(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $this->filters->save($this->auth->user()['id'], 'taxi-log', trim($_POST['filter_name']), $this->collectFiltersFromPost());
        header('Location: /taxi-log');
    }

    public function export(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $filters = $this->collectFiltersFromPost();
        $entries = $this->model->latest($filters);
        if (($_POST['format'] ?? 'pdf') === 'excel') {
            ExportExcel::fromArray($entries, 'taxi-log.xlsx');
            return;
        }
        $html = '<h1>Taxi log</h1><table border="1"><tr><th>Date</th><th>Route</th><th>Guest</th><th>Price</th></tr>';
        foreach ($entries as $entry) {
            $html .= '<tr><td>' . htmlspecialchars($entry['ride_time']) . '</td><td>' . htmlspecialchars($entry['start_location'] . ' → ' . $entry['destination']) . '</td><td>' . htmlspecialchars($entry['guest_name']) . '</td><td>' . htmlspecialchars($entry['price']) . '</td></tr>';
        }
        $html .= '</table>';
        ExportPdf::fromHtml($html, 'taxi-log.pdf');
    }

    private function collectFilters(): array
    {
        return [
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'driver' => $_GET['driver'] ?? null,
            'guest' => $_GET['guest'] ?? null,
            'room' => $_GET['room'] ?? null,
            'month' => $_GET['month'] ?? date('Y-m'),
        ];
    }

    private function collectFiltersFromPost(): array
    {
        return [
            'date_from' => $_POST['date_from'] ?? null,
            'date_to' => $_POST['date_to'] ?? null,
            'driver' => $_POST['driver'] ?? null,
            'guest' => $_POST['guest'] ?? null,
            'room' => $_POST['room'] ?? null,
            'month' => $_POST['month'] ?? date('Y-m'),
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
