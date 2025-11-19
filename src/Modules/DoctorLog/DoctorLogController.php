<?php
namespace Modules\DoctorLog;

use Core\Controller;
use Core\CSRF;
use Core\ExportExcel;
use Core\ExportPdf;
use Core\SavedFilters;

class DoctorLogController extends Controller
{
    private DoctorLogModel $model;
    private SavedFilters $filters;

    public function __construct()
    {
        parent::__construct();
        $this->model = new DoctorLogModel($this->auth->user());
        $this->filters = new SavedFilters();
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = $this->collectFilters();
        $entries = $this->model->list($filters);
        $saved = $this->filters->all($this->auth->user()['id'], 'doctor-log');
        $this->view->render('doctor_log/index', [
            'user' => $this->auth->user(),
            'entries' => $entries,
            'filters' => $filters,
            'savedFilters' => $saved,
            'csrf' => CSRF::token(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePermission('doctorlog.manage');
        $this->guardCsrf();
        $this->model->create([
            'room_number' => $_POST['room_number'],
            'time_called' => $this->formatDateTime($_POST['time_called'] ?? ''),
            'time_arrived' => $_POST['time_arrived'] ? $this->formatDateTime($_POST['time_arrived']) : null,
            'doctor_name' => $_POST['doctor_name'],
            'reason' => $_POST['reason'] ?? '',
            'status' => $_POST['status'] ?? 'open',
            'user_id' => $this->auth->user()['id'],
        ]);
        header('Location: /doctor-log');
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->requirePermission('doctorlog.manage');
        $this->guardCsrf();
        $this->model->softDelete((int)$_POST['entry_id']);
        header('Location: /doctor-log');
    }

    public function saveFilter(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $this->filters->save($this->auth->user()['id'], 'doctor-log', trim($_POST['filter_name']), $this->collectFiltersFromPost());
        header('Location: /doctor-log');
    }

    public function export(): void
    {
        $this->requireAuth();
        $this->guardCsrf();
        $entries = $this->model->list($this->collectFiltersFromPost());
        if (($_POST['format'] ?? 'pdf') === 'excel') {
            ExportExcel::fromArray($entries, 'doctor-log.xlsx');
            return;
        }
        $html = '<h1>Doctor log</h1><table border="1"><tr><th>Room</th><th>Called</th><th>Doctor</th><th>Status</th></tr>';
        foreach ($entries as $entry) {
            $html .= '<tr><td>' . htmlspecialchars($entry['room_number']) . '</td><td>' . htmlspecialchars($entry['time_called']) . '</td><td>' . htmlspecialchars($entry['doctor_name']) . '</td><td>' . htmlspecialchars($entry['status']) . '</td></tr>';
        }
        $html .= '</table>';
        ExportPdf::fromHtml($html, 'doctor-log.pdf');
    }

    private function collectFilters(): array
    {
        return [
            'status' => $_GET['status'] ?? null,
            'room' => $_GET['room'] ?? null,
            'doctor' => $_GET['doctor'] ?? null,
            'from' => $_GET['from'] ?? null,
            'to' => $_GET['to'] ?? null,
        ];
    }

    private function collectFiltersFromPost(): array
    {
        return [
            'status' => $_POST['status'] ?? null,
            'room' => $_POST['room'] ?? null,
            'doctor' => $_POST['doctor'] ?? null,
            'from' => $_POST['from'] ?? null,
            'to' => $_POST['to'] ?? null,
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
