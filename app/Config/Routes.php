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

/*
|--------------------------------------------------------------------------
| Admin APIs
|--------------------------------------------------------------------------
*/

$routes->group(
    'api/v1/admin',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get(
            'roles',
            'Api\V1\Admin\RoleController::index',
            ['filter' => 'permission:roles-view']
        );

        $routes->post(
            'roles',
            'Api\V1\Admin\RoleController::create',
            ['filter' => 'permission:roles-create']
        );

        $routes->get(
            'roles/(:segment)',
            'Api\V1\Admin\RoleController::show/$1',
            ['filter' => 'permission:roles-view']
        );

        $routes->put(
            'roles/(:segment)',
            'Api\V1\Admin\RoleController::update/$1',
            ['filter' => 'permission:roles-update']
        );

        $routes->delete(
            'roles/(:segment)',
            'Api\V1\Admin\RoleController::delete/$1',
            ['filter' => 'permission:roles-delete']
        );

        $routes->get(
            'permissions',
            'Api\V1\Admin\PermissionController::index',
            [
                'filter' => 'permission:permission-view'
            ]
        );

        $routes->post(
            'permissions',
            'Api\V1\Admin\PermissionController::create',
            [
                'filter' => 'permission:permission-create'
            ]
        );

        $routes->get(
            'permissions/(:segment)',
            'Api\V1\Admin\PermissionController::show/$1',
            [
                'filter' => 'permission:permission-view'
            ]
        );

        $routes->put(
            'permissions/(:segment)',
            'Api\V1\Admin\PermissionController::update/$1',
            [
                'filter' => 'permission:permission-edit'
            ]
        );

        $routes->delete(
            'permissions/(:segment)',
            'Api\V1\Admin\PermissionController::delete/$1',
            [
                'filter' => 'permission:permission-delete'
            ]
        );

        $routes->get(
            'roles/(:segment)/permissions',
            'Api\V1\Admin\RolePermissionController::index/$1',
            [
                'filter' => 'permission:role-permissions-view',
            ]
        );

        $routes->post(
            'roles/(:segment)/permissions',
            'Api\V1\Admin\RolePermissionController::assign/$1',
            [
                'filter' => 'permission:role-permissions-assign',
            ]
        );

        $routes->get(
            'profiles',
            'Api\V1\Admin\ProfileController::index',
            [
                'filter' => 'permission:profile-view'
            ]
        );

        $routes->post(
            'profiles',
            'Api\V1\Admin\ProfileController::create',
            [
                'filter' => 'permission:profile-create'
            ]
        );

        $routes->get(
            'profiles/(:segment)',
            'Api\V1\Admin\ProfileController::show/$1',
            [
                'filter' => 'permission:profile-view'
            ]
        );

        $routes->put(
            'profiles/(:segment)',
            'Api\V1\Admin\ProfileController::update/$1',
            [
                'filter' => 'permission:profile-edit'
            ]
        );

        $routes->delete(
            'profiles/(:segment)',
            'Api\V1\Admin\ProfileController::delete/$1',
            [
                'filter' => 'permission:profile-delete'
            ]
        );
    }
);

/*
|--------------------------------------------------------------------------
| Public APIs
|--------------------------------------------------------------------------
*/

$routes->group('api/v1/public', static function ($routes) {});
