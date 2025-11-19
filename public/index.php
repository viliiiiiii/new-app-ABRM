<?php
require_once __DIR__ . '/../bootstrap.php';

use Core\Router;
use Modules\Landing\LandingController;
use Modules\LostAndFound\LostAndFoundController;
use Modules\TaxiLog\TaxiLogController;
use Modules\Inventory\InventoryController;
use Modules\DoctorLog\DoctorLogController;
use Modules\Notes\NotesController;
use Modules\UserProfile\UserProfileController;
use Modules\UserManagement\UserManagementController;
use Modules\SectorManagement\SectorManagementController;
use Modules\UserActivity\UserActivityController;
use Modules\CMS\CmsController;

$router = new Router();

$router->get('/', function () {
    (new LandingController())->index();
});
$router->get('/lost-and-found', function () {
    (new LostAndFoundController())->index();
});
$router->post('/lost-and-found', function () {
    (new LostAndFoundController())->store();
});
$router->get('/taxi-log', function () {
    (new TaxiLogController())->index();
});
$router->get('/inventory', function () {
    (new InventoryController())->index();
});
$router->get('/doctor-log', function () {
    (new DoctorLogController())->index();
});
$router->get('/notes', function () {
    (new NotesController())->index();
});
$router->get('/profile', function () {
    (new UserProfileController())->index();
});
$router->get('/users', function () {
    (new UserManagementController())->index();
});
$router->get('/sectors', function () {
    (new SectorManagementController())->index();
});
$router->get('/activity', function () {
    (new UserActivityController())->index();
});
$router->get('/cms', function () {
    (new CmsController())->index();
});

$router->dispatch(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $_SERVER['REQUEST_METHOD']);
