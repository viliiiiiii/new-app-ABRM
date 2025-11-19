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
$router->get('/lost-and-found/recycle-bin', function () {
    (new LostAndFoundController())->recycleBin();
});
$router->post('/lost-and-found/change-state', function () {
    (new LostAndFoundController())->changeState();
});
$router->post('/lost-and-found/delete', function () {
    (new LostAndFoundController())->delete();
});
$router->post('/lost-and-found/restore', function () {
    (new LostAndFoundController())->restore();
});
$router->post('/lost-and-found/release', function () {
    (new LostAndFoundController())->release();
});
$router->post('/lost-and-found/save-filter', function () {
    (new LostAndFoundController())->saveFilter();
});
$router->post('/lost-and-found/export', function () {
    (new LostAndFoundController())->export();
});
$router->post('/lost-and-found/update-text', function () {
    (new LostAndFoundController())->updateText();
});
$router->get('/taxi-log', function () {
    (new TaxiLogController())->index();
});
$router->post('/taxi-log', function () {
    (new TaxiLogController())->store();
});
$router->post('/taxi-log/delete', function () {
    (new TaxiLogController())->delete();
});
$router->post('/taxi-log/save-filter', function () {
    (new TaxiLogController())->saveFilter();
});
$router->post('/taxi-log/export', function () {
    (new TaxiLogController())->export();
});
$router->get('/inventory', function () {
    (new InventoryController())->index();
});
$router->post('/inventory', function () {
    (new InventoryController())->store();
});
$router->post('/inventory/movement', function () {
    (new InventoryController())->movement();
});
$router->post('/inventory/start-stocktake', function () {
    (new InventoryController())->startStocktake();
});
$router->post('/inventory/update-stocktake', function () {
    (new InventoryController())->updateStocktake();
});
$router->post('/inventory/save-filter', function () {
    (new InventoryController())->saveFilter();
});
$router->post('/inventory/export', function () {
    (new InventoryController())->export();
});
$router->get('/doctor-log', function () {
    (new DoctorLogController())->index();
});
$router->post('/doctor-log', function () {
    (new DoctorLogController())->store();
});
$router->post('/doctor-log/delete', function () {
    (new DoctorLogController())->delete();
});
$router->post('/doctor-log/save-filter', function () {
    (new DoctorLogController())->saveFilter();
});
$router->post('/doctor-log/export', function () {
    (new DoctorLogController())->export();
});
$router->get('/notes', function () {
    (new NotesController())->index();
});
$router->post('/notes', function () {
    (new NotesController())->store();
});
$router->post('/notes/checklist', function () {
    (new NotesController())->toggleChecklist();
});
$router->post('/notes/comment', function () {
    (new NotesController())->comment();
});
$router->post('/notes/share', function () {
    (new NotesController())->share();
});
$router->post('/notes/pin', function () {
    (new NotesController())->pin();
});
$router->post('/notes/favourite', function () {
    (new NotesController())->favourite();
});
$router->post('/notes/save-filter', function () {
    (new NotesController())->saveFilter();
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
$router->post('/activity/export', function () {
    (new UserActivityController())->export();
});
$router->get('/cms', function () {
    (new CmsController())->index();
});

$router->dispatch(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $_SERVER['REQUEST_METHOD']);
