<?php
namespace Modules\SectorManagement;

use Core\Controller;
use Core\Database;
use Core\CSRF;

class SectorManagementController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('sectors.manage');
        $db = Database::connection();
        $stmt = $db->query('SELECT s.*, GROUP_CONCAT(u.name) as supervisors FROM sectors s LEFT JOIN sector_supervisors ss ON ss.sector_id = s.id LEFT JOIN users u ON u.id = ss.user_id GROUP BY s.id');
        $sectors = $stmt->fetchAll();
        $this->view->render('sector_management/index', [
            'user' => $this->auth->user(),
            'sectors' => $sectors,
            'csrf' => CSRF::token(),
        ]);
    }
}
