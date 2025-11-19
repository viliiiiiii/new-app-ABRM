<?php
namespace Modules\CMS;

use Core\Controller;
use Core\CSRF;

class CmsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('cms.manage');
        $this->view->render('cms/index', [
            'user' => $this->auth->user(),
            'csrf' => CSRF::token(),
        ]);
    }
}
