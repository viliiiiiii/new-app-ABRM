<?php
namespace Core;

abstract class Controller
{
    protected Auth $auth;
    protected View $view;
    protected Permissions $permissions;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->permissions = new Permissions($this->auth->user());
    }

    protected function requireAuth(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }
    }

    protected function requirePermission(string $permission): void
    {
        if (!$this->permissions->allows($permission)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}
