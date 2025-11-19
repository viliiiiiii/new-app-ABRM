<?php
namespace Modules\UserActivity;

use Core\Controller;
use Core\CSRF;
use Core\ExportExcel;
use Core\ExportPdf;

class UserActivityController extends Controller
{
    private UserActivityModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new UserActivityModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('activity.view');

        $filters = $this->collectFilters($_GET);
        $entries = $this->model->entries($filters);
        $detailId = isset($_GET['view']) ? (int)$_GET['view'] : null;
        $detail = $detailId ? $this->model->detail($detailId) : null;
        $diff = $detail ? $this->buildDiff($detail) : [];
        $options = $this->model->filterOptions();
        $summary = $this->summariseBySeverity($entries);

        $this->view->render('user_activity/index', [
            'user' => $this->auth->user(),
            'entries' => $entries,
            'filters' => $filters,
            'detail' => $detail,
            'diff' => $diff,
            'options' => $options,
            'summary' => $summary,
            'csrf' => CSRF::token(),
        ]);
    }

    public function export(): void
    {
        $this->requireAuth();
        $this->requirePermission('activity.view');
        $this->guardCsrf();
        $filters = $this->collectFilters($_POST);
        $format = $_POST['format'] ?? 'pdf';
        $rows = $this->model->entries($filters, 1000);
        $flat = array_map(function ($entry) {
            return [
                'Time' => $entry['created_at'],
                'User' => $entry['user_name'] ?? 'System',
                'Module' => $entry['module'],
                'Action' => $entry['action_type'],
                'Severity' => strtoupper($entry['severity'] ?? 'info'),
                'Description' => $entry['description'],
            ];
        }, $rows);
        if ($format === 'excel') {
            ExportExcel::fromArray($flat, 'activity_log.xlsx');
            return;
        }
        $html = '<h1>Activity log export</h1><table border="1" cellpadding="6" cellspacing="0">';
        $html .= '<tr><th>Time</th><th>User</th><th>Module</th><th>Action</th><th>Severity</th><th>Description</th></tr>';
        foreach ($flat as $item) {
            $html .= '<tr>';
            foreach ($item as $value) {
                $html .= '<td>' . htmlspecialchars((string)$value) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        ExportPdf::fromHtml($html, 'activity_log.pdf');
    }

    private function collectFilters(array $source): array
    {
        return [
            'user_id' => !empty($source['user_id']) ? (int)$source['user_id'] : null,
            'module' => $source['module'] ?? null,
            'action' => $source['action'] ?? null,
            'severity' => $source['severity'] ?? null,
            'from' => $source['from'] ?? null,
            'to' => $source['to'] ?? null,
            'query' => $source['query'] ?? null,
        ];
    }

    private function buildDiff(array $entry): array
    {
        $before = $entry['before_state'] ?? [];
        $after = $entry['after_state'] ?? [];
        if (!is_array($before)) {
            $before = [];
        }
        if (!is_array($after)) {
            $after = [];
        }
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $diff = [];
        foreach ($keys as $key) {
            $prev = $before[$key] ?? null;
            $next = $after[$key] ?? null;
            if ($prev !== $next) {
                $diff[] = [
                    'field' => $key,
                    'before' => $prev,
                    'after' => $next,
                ];
            }
        }
        return $diff;
    }

    private function summariseBySeverity(array $entries): array
    {
        $summary = ['total' => count($entries), 'critical' => 0, 'warning' => 0, 'info' => 0];
        foreach ($entries as $entry) {
            $severity = $entry['severity'] ?? 'info';
            if (!isset($summary[$severity])) {
                $summary[$severity] = 0;
            }
            $summary[$severity]++;
        }
        return $summary;
    }

    private function guardCsrf(): void
    {
        if (!CSRF::validate($_POST['_token'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF token');
        }
    }
}
