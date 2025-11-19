<?php
namespace Modules\UserManagement;

use Core\Controller;
use Core\Database;
use Core\CSRF;

class UserManagementController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('users.manage');
        $db = Database::connection();
        $stmt = $db->query('SELECT u.id, u.name, u.email, r.name as role_name, s.name as sector_name, u.status FROM users u LEFT JOIN roles r ON r.id = u.role_id LEFT JOIN sectors s ON s.id = u.sector_id');
        $users = $stmt->fetchAll();
        $this->view->render('user_management/index', [
            'user' => $this->auth->user(),
            'users' => $users,
            'csrf' => CSRF::token(),
        ]);
    }
}
