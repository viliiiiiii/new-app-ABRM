<?php
namespace Modules\UserActivity;

use Core\Controller;
use Core\Database;
use Core\CSRF;

class UserActivityController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('activity.view');
        $db = Database::connection();
        $stmt = $db->query('SELECT a.*, u.name as user_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 200');
        $entries = $stmt->fetchAll();
        $this->view->render('user_activity/index', [
            'user' => $this->auth->user(),
            'entries' => $entries,
            'csrf' => CSRF::token(),
        ]);
    }
}
