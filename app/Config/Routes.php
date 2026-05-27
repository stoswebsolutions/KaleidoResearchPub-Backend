<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

$routes->options('(:any)', function () {
    return service('response')
        ->setHeader('Access-Control-Allow-Origin', '*')
        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->setStatusCode(200);
});


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

        $routes->get(
            'article-types',
            'Api\V1\Admin\ArticleTypeController::index',
            ['filter' => 'permission:article-type-view']
        );

        $routes->post(
            'article-types',
            'Api\V1\Admin\ArticleTypeController::create',
            ['filter' => 'permission:article-type-create']
        );

        $routes->get(
            'article-types/(:segment)',
            'Api\V1\Admin\ArticleTypeController::show/$1',
            ['filter' => 'permission:article-type-view']
        );

        $routes->put(
            'article-types/(:segment)',
            'Api\V1\Admin\ArticleTypeController::update/$1',
            ['filter' => 'permission:article-type-edit']
        );

        $routes->delete(
            'article-types/(:segment)',
            'Api\V1\Admin\ArticleTypeController::delete/$1',
            ['filter' => 'permission:article-type-delete']
        );

        $routes->get(
            'journals',
            'Api\V1\Admin\JournalController::index',
            ['filter' => 'permission:journal-view']
        );

        $routes->post(
            'journals',
            'Api\V1\Admin\JournalController::create',
            ['filter' => 'permission:journal-create']
        );

        $routes->get(
            'journals/(:segment)',
            'Api\V1\Admin\JournalController::show/$1',
            ['filter' => 'permission:journal-view']
        );

        $routes->put(
            'journals/(:segment)',
            'Api\V1\Admin\JournalController::update/$1',
            ['filter' => 'permission:journal-edit']
        );

        $routes->delete(
            'journals/(:segment)',
            'Api\V1\Admin\JournalController::delete/$1',
            ['filter' => 'permission:journal-delete']
        );

        $routes->get(
            'disciplines',
            'Api\V1\Admin\DisciplineController::index',
            ['filter' => 'permission:discipline-view']
        );

        $routes->post(
            'disciplines',
            'Api\V1\Admin\DisciplineController::create',
            ['filter' => 'permission:discipline-create']
        );

        $routes->get(
            'disciplines/(:segment)',
            'Api\V1\Admin\DisciplineController::show/$1',
            ['filter' => 'permission:discipline-view']
        );

        $routes->put(
            'disciplines/(:segment)',
            'Api\V1\Admin\DisciplineController::update/$1',
            ['filter' => 'permission:discipline-edit']
        );

        $routes->delete(
            'disciplines/(:segment)',
            'Api\V1\Admin\DisciplineController::delete/$1',
            ['filter' => 'permission:discipline-delete']
        );
    }
);

/*
|--------------------------------------------------------------------------
| Public APIs
|--------------------------------------------------------------------------
*/

$routes->group('api/v1/public', static function ($routes) {
    $routes->get(
        'article-types',
        'Api\V1\Public\ArticleTypeController::index'
    );

    $routes->get(
        'article-types/(:segment)',
        'Api\V1\Public\ArticleTypeController::show/$1'
    );

    $routes->get(
        'journals',
        'Api\V1\Public\JournalController::index'
    );

    $routes->get(
        'journals/(:segment)',
        'Api\V1\Public\JournalController::show/$1'
    );

    $routes->get(
        'disciplines',
        'Api\V1\Public\DisciplineController::index'
    );

    $routes->get(
        'disciplines/(:segment)',
        'Api\V1\Public\DisciplineController::show/$1'
    );
    
});
