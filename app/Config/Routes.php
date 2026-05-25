<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('api/v1/auth', static function ($routes) {

    $routes->post(
        'login',
        'Api\V1\AuthController::login'
    );

    $routes->get(
        'me',
        'Api\V1\AuthController::me',
        [
            'filter' => 'auth',
        ]
    );

    $routes->post(
        'refresh-token',
        'Api\V1\AuthController::refreshToken'
    );

    $routes->post(
        'logout',
        'Api\V1\AuthController::logout',
        [
            'filter' => 'auth',
        ]
    );

    $routes->post(
        'logout-all',
        'Api\V1\AuthController::logoutAll',
        [
            'filter' => 'auth',
        ]
    );

    $routes->post(
        'forgot-password',
        'Api\V1\AuthController::forgotPassword'
    );

    $routes->post(
        'reset-password',
        'Api\V1\AuthController::resetPassword'
    );

});

$routes->group('api/v1', ['filter' => 'auth'], static function ($routes) {

    $routes->get(
        'roles',
        'RoleController::index',
        [
            'filter' => 'auth,permission:role-view'
        ]
    );

    $routes->post(
        'roles',
        'RoleController::create',
        [
            'filter' => 'auth,permission:role-create'
        ]
    );

    $routes->put(
        'roles/(:num)',
        'RoleController::update/$1',
        [
            'filter' => 'auth,permission:role-edit'
        ]
    );

    $routes->delete(
        'roles/(:num)',
        'RoleController::delete/$1',
        [
            'filter' => 'auth,permission:role-delete'
        ]
    );

});