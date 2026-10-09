<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::login');
$routes->post('logout', 'AuthController::logout', ['filter' => 'adminauth']);
$routes->get('csrf-token', 'CsrfController::token', ['filter' => 'adminauth']);
foreach (['data', 'layers', 'resources', 'styles', 'webfonts'] as $assetDirectory) {
    $routes->get($assetDirectory . '/(:any)', 'AssetController::serve/' . $assetDirectory . '/$1', ['filter' => 'adminauth']);
}
$routes->get('/', 'MapController::index', ['filter' => 'adminauth']);
$routes->get('map-data', 'MapDataController::index', ['filter' => 'adminauth']);
$routes->get('map-data/(:segment)', 'MapDataController::serve/$1', ['filter' => 'adminauth']);
$routes->post('save_customer_csv.php', 'CsvController::save', ['filter' => 'adminauth']);
$routes->group('admin', ['filter' => 'adminauth:admin'], static function ($routes): void {
    $routes->get('/', 'AdminController::dashboard');
    $routes->get('diagram', 'AdminController::diagram');
    $routes->get('data', 'AdminController::data');
    $routes->get('upload', 'AdminController::upload');
    $routes->get('users', 'AdminController::users');
    $routes->post('users', 'AdminController::createUser');
    $routes->get('users/(:num)', 'AdminController::viewUser/$1');
    $routes->get('users/(:num)/edit', 'AdminController::editUser/$1');
    $routes->post('users/(:num)/edit', 'AdminController::updateUser/$1');
    $routes->post('users/(:num)/delete', 'AdminController::deleteUser/$1');
    $routes->post('map-data', 'MapDataController::upload');
});
