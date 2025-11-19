<?php
namespace Modules\Landing;

use Core\Controller;

class LandingController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view->render('landing/index', [
            'user' => $this->auth->user(),
        ]);
    }
}
