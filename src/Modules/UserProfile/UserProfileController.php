<?php
namespace Modules\UserProfile;

use Core\Controller;
use Core\CSRF;
use Core\Database;

class UserProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = $this->auth->user();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT ip_address, user_agent, created_at FROM login_history WHERE user_id = :id ORDER BY created_at DESC LIMIT 5');
        $stmt->execute(['id' => $user['id']]);
        $history = $stmt->fetchAll();
        $this->view->render('user_profile/index', [
            'user' => $user,
            'history' => $history,
            'csrf' => CSRF::token(),
        ]);
    }
}
