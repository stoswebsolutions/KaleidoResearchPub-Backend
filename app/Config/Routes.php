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
            ['filter' => 'permission:roles-edit']
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

        $routes->get(
            'subscription-plans',
            'Api\V1\Admin\SubscriptionPlanController::index',
            ['filter' => 'permission:subscription-plan-view']
        );

        $routes->post(
            'subscription-plans',
            'Api\V1\Admin\SubscriptionPlanController::create',
            ['filter' => 'permission:subscription-plan-create']
        );

        $routes->get(
            'subscription-plans/(:segment)',
            'Api\V1\Admin\SubscriptionPlanController::show/$1',
            ['filter' => 'permission:subscription-plan-view']
        );

        $routes->put(
            'subscription-plans/(:segment)',
            'Api\V1\Admin\SubscriptionPlanController::update/$1',
            ['filter' => 'permission:subscription-plan-edit']
        );

        $routes->delete(
            'subscription-plans/(:segment)',
            'Api\V1\Admin\SubscriptionPlanController::delete/$1',
            ['filter' => 'permission:subscription-plan-delete']
        );

        $routes->get(
            'academic-partners',
            'Api\V1\Admin\AcademicPartnerController::index',
            ['filter' => 'permission:academic-partner-view']
        );

        $routes->post(
            'academic-partners',
            'Api\V1\Admin\AcademicPartnerController::create',
            ['filter' => 'permission:academic-partner-create']
        );

        $routes->get(
            'academic-partners/(:segment)',
            'Api\V1\Admin\AcademicPartnerController::show/$1',
            ['filter' => 'permission:academic-partner-view']
        );

        $routes->put(
            'academic-partners/(:segment)',
            'Api\V1\Admin\AcademicPartnerController::update/$1',
            ['filter' => 'permission:academic-partner-edit']
        );

        $routes->delete(
            'academic-partners/(:segment)',
            'Api\V1\Admin\AcademicPartnerController::delete/$1',
            ['filter' => 'permission:academic-partner-delete']
        );

        $routes->get(
            'indexed-partners',
            'Api\V1\Admin\IndexedPartnerController::index',
            ['filter' => 'permission:indexed-partner-view']
        );

        $routes->post(
            'indexed-partners',
            'Api\V1\Admin\IndexedPartnerController::create',
            ['filter' => 'permission:indexed-partner-create']
        );

        $routes->get(
            'indexed-partners/(:segment)',
            'Api\V1\Admin\IndexedPartnerController::show/$1',
            ['filter' => 'permission:indexed-partner-view']
        );

        $routes->put(
            'indexed-partners/(:segment)',
            'Api\V1\Admin\IndexedPartnerController::update/$1',
            ['filter' => 'permission:indexed-partner-edit']
        );

        $routes->delete(
            'indexed-partners/(:segment)',
            'Api\V1\Admin\IndexedPartnerController::delete/$1',
            ['filter' => 'permission:indexed-partner-delete']
        );

        $routes->get(
            'cms-features',
            'Api\V1\Admin\CmsFeatureController::index',
            ['filter' => 'permission:cms-feature-view']
        );

        $routes->post(
            'cms-features',
            'Api\V1\Admin\CmsFeatureController::create',
            ['filter' => 'permission:cms-feature-create']
        );

        $routes->get(
            'cms-features/(:segment)',
            'Api\V1\Admin\CmsFeatureController::show/$1',
            ['filter' => 'permission:cms-feature-view']
        );

        $routes->put(
            'cms-features/(:segment)',
            'Api\V1\Admin\CmsFeatureController::update/$1',
            ['filter' => 'permission:cms-feature-edit']
        );

        $routes->delete(
            'cms-features/(:segment)',
            'Api\V1\Admin\CmsFeatureController::delete/$1',
            ['filter' => 'permission:cms-feature-delete']
        );

        $routes->get(
            'cms-pages',
            'Api\V1\Admin\CmsPageController::index',
            ['filter' => 'permission:cms-page-view']
        );

        $routes->post(
            'cms-pages',
            'Api\V1\Admin\CmsPageController::create',
            ['filter' => 'permission:cms-page-create']
        );

        $routes->get(
            'cms-pages/(:segment)',
            'Api\V1\Admin\CmsPageController::show/$1',
            ['filter' => 'permission:cms-page-view']
        );

        $routes->put(
            'cms-pages/(:segment)',
            'Api\V1\Admin\CmsPageController::update/$1',
            ['filter' => 'permission:cms-page-edit']
        );

        $routes->delete(
            'cms-pages/(:segment)',
            'Api\V1\Admin\CmsPageController::delete/$1',
            ['filter' => 'permission:cms-page-delete']
        );

        $routes->get(
            'contact-settings',
            'Api\V1\Admin\ContactSettingController::index',
            ['filter' => 'permission:contact-setting-view']
        );

        $routes->post(
            'contact-settings',
            'Api\V1\Admin\ContactSettingController::create',
            ['filter' => 'permission:contact-setting-create']
        );

        $routes->get(
            'contact-settings/(:segment)',
            'Api\V1\Admin\ContactSettingController::show/$1',
            ['filter' => 'permission:contact-setting-view']
        );

        $routes->put(
            'contact-settings/(:segment)',
            'Api\V1\Admin\ContactSettingController::update/$1',
            ['filter' => 'permission:contact-setting-edit']
        );

        $routes->delete(
            'contact-settings/(:segment)',
            'Api\V1\Admin\ContactSettingController::delete/$1',
            ['filter' => 'permission:contact-setting-delete']
        );

        $routes->get(
            'contact-messages',
            'Api\V1\Admin\ContactMessageController::index',
            ['filter' => 'permission:contact-message-view']
        );

        $routes->post(
            'contact-messages',
            'Api\V1\Admin\ContactMessageController::create',
            ['filter' => 'permission:contact-message-create']
        );

        $routes->get(
            'contact-messages/(:segment)',
            'Api\V1\Admin\ContactMessageController::show/$1',
            ['filter' => 'permission:contact-message-view']
        );

        $routes->put(
            'contact-messages/(:segment)',
            'Api\V1\Admin\ContactMessageController::update/$1',
            ['filter' => 'permission:contact-message-edit']
        );

        $routes->delete(
            'contact-messages/(:segment)',
            'Api\V1\Admin\ContactMessageController::delete/$1',
            ['filter' => 'permission:contact-message-delete']
        );

        $routes->patch(
            'contact-messages/(:segment)/mark-read',
            'Api\V1\Admin\ContactMessageController::markRead/$1',
            ['filter' => 'permission:contact-message-edit']
        );

        $routes->patch(
            'contact-messages/(:segment)/mark-unread',
            'Api\V1\Admin\ContactMessageController::markUnread/$1',
            ['filter' => 'permission:contact-message-edit']
        );

        $routes->patch(
            'contact-messages/(:segment)/resolve',
            'Api\V1\Admin\ContactMessageController::markResolved/$1',
            ['filter' => 'permission:contact-message-edit']
        );

        $routes->get(
            'editor-profiles',
            'Api\V1\Admin\EditorProfileController::index',
            ['filter' => 'permission:editor-profile-view']
        );

        $routes->post(
            'editor-profiles',
            'Api\V1\Admin\EditorProfileController::create',
            ['filter' => 'permission:editor-profile-create']
        );

        $routes->get(
            'editor-profiles/(:segment)',
            'Api\V1\Admin\EditorProfileController::show/$1',
            ['filter' => 'permission:editor-profile-view']
        );

        $routes->put(
            'editor-profiles/(:segment)',
            'Api\V1\Admin\EditorProfileController::update/$1',
            ['filter' => 'permission:editor-profile-edit']
        );

        $routes->delete(
            'editor-profiles/(:segment)',
            'Api\V1\Admin\EditorProfileController::delete/$1',
            ['filter' => 'permission:editor-profile-delete']
        );

        $routes->get(
            'author-profiles',
            'Api\V1\Admin\AuthorProfileController::index',
            ['filter' => 'permission:author-profile-view']
        );

        $routes->post(
            'author-profiles',
            'Api\V1\Admin\AuthorProfileController::create',
            ['filter' => 'permission:author-profile-create']
        );

        $routes->get(
            'author-profiles/(:segment)',
            'Api\V1\Admin\AuthorProfileController::show/$1',
            ['filter' => 'permission:author-profile-view']
        );

        $routes->put(
            'author-profiles/(:segment)',
            'Api\V1\Admin\AuthorProfileController::update/$1',
            ['filter' => 'permission:author-profile-edit']
        );

        $routes->delete(
            'author-profiles/(:segment)',
            'Api\V1\Admin\AuthorProfileController::delete/$1',
            ['filter' => 'permission:author-profile-delete']
        );

        $routes->get(
            'account-details',
            'Api\V1\Admin\AccountDetailsController::index',
            ['filter' => 'permission:account-details-view']
        );

        $routes->post(
            'account-details',
            'Api\V1\Admin\AccountDetailsController::create',
            ['filter' => 'permission:account-details-create']
        );

        $routes->get(
            'account-details/(:segment)',
            'Api\V1\Admin\AccountDetailsController::show/$1',
            ['filter' => 'permission:account-details-view']
        );

        $routes->put(
            'account-details/(:segment)',
            'Api\V1\Admin\AccountDetailsController::update/$1',
            ['filter' => 'permission:account-details-edit']
        );

        $routes->delete(
            'account-details/(:segment)',
            'Api\V1\Admin\AccountDetailsController::delete/$1',
            ['filter' => 'permission:account-details-delete']
        );

        $routes->get(
            'manuscripts',
            'Api\V1\Admin\ManuscriptController::index',
            ['filter' => 'permission:manuscript-view']
        );

        $routes->get(
            'manuscripts/(:segment)',
            'Api\V1\Admin\ManuscriptController::show/$1',
            ['filter' => 'permission:manuscript-view']
        );

        $routes->get(
            'manuscripts/(:segment)/timeline',
            'Api\V1\Admin\ManuscriptController::timeline/$1',
            ['filter' => 'permission:manuscript-view']
        );

        $routes->post(
            'manuscripts/(:segment)/review',
            'Api\V1\Admin\ManuscriptController::submitReview/$1',
            ['filter' => 'permission:manuscript-review']
        );

        $routes->post(
            'manuscripts/(:segment)/decision',
            'Api\V1\Admin\ManuscriptController::decision/$1',
            ['filter' => 'permission:manuscript-decision']
        );

        $routes->post(
            'manuscripts/(:segment)/verify-payment',
            'Api\V1\Admin\ManuscriptController::verifyPayment/$1',
            ['filter' => 'permission:manuscript-payment-verify']
        );

        $routes->post(
            'manuscripts/(:segment)/publish',
            'Api\V1\Admin\ManuscriptController::publish/$1',
            ['filter' => 'permission:manuscript-publish']
        );
    }
);

/*
|--------------------------------------------------------------------------
| Public APIs
|--------------------------------------------------------------------------
*/

$routes->group('api/v1/public', static function ($routes) {

    $routes->post(
        'profiles/register',
        'Api\V1\Public\ProfileController::register'
    );

    $routes->get(
        'profiles/(:segment)',
        'Api\V1\Public\ProfileController::show/$1'
    );

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

    $routes->get(
        'subscription-plans',
        'Api\V1\Public\SubscriptionPlanController::index'
    );

    $routes->get(
        'subscription-plans/(:segment)',
        'Api\V1\Public\SubscriptionPlanController::show/$1'
    );

    $routes->get(
        'academic-partners',
        'Api\V1\Public\AcademicPartnerController::index'
    );

    $routes->get(
        'academic-partners/(:segment)',
        'Api\V1\Public\AcademicPartnerController::show/$1'
    );

    $routes->get(
        'indexed-partners',
        'Api\V1\Public\IndexedPartnerController::index'
    );

    $routes->get(
        'indexed-partners/(:segment)',
        'Api\V1\Public\IndexedPartnerController::show/$1'
    );

    $routes->get(
        'cms-features',
        'Api\V1\Public\CmsFeatureController::index'
    );

    $routes->get(
        'cms-features/(:segment)',
        'Api\V1\Public\CmsFeatureController::show/$1'
    );

    $routes->get(
        'cms-pages',
        'Api\V1\Public\CmsPageController::index'
    );

    $routes->get(
        'cms-pages/page-key/(:segment)',
        'Api\V1\Public\CmsPageController::showByPageKey/$1'
    );

    $routes->get(
        'cms-pages/(:segment)',
        'Api\V1\Public\CmsPageController::show/$1'
    );

    $routes->get(
        'contact-settings',
        'Api\V1\Public\ContactSettingController::index'
    );

    $routes->get(
        'contact-settings/active',
        'Api\V1\Public\ContactSettingController::active'
    );

    $routes->get(
        'contact-settings/(:segment)',
        'Api\V1\Public\ContactSettingController::show/$1'
    );

    $routes->post(
        'contact-messages',
        'Api\V1\Public\ContactMessageController::create'
    );

    $routes->get(
        'contact-messages/(:segment)',
        'Api\V1\Public\ContactMessageController::show/$1'
    );

    $routes->get(
        'editor-profiles',
        'Api\V1\Public\EditorProfileController::index'
    );

    $routes->get(
        'editor-profiles/(:segment)',
        'Api\V1\Public\EditorProfileController::show/$1'
    );

    $routes->get(
        'account-details',
        'Api\V1\Public\AccountDetailsController::index'
    );

    $routes->get(
        'account-details/(:segment)',
        'Api\V1\Public\AccountDetailsController::show/$1'
    );

    $routes->post(
        'manuscripts',
        'Api\V1\Public\ManuscriptController::submit'
    );

    $routes->post(
        'manuscripts/request-tracking-otp',
        'Api\V1\Public\ManuscriptController::requestTrackingOtp'
    );

    $routes->post(
        'manuscripts/verify-tracking-otp',
        'Api\V1\Public\ManuscriptController::verifyTrackingOtp'
    );

    $routes->post(
        'manuscripts/upload-payment',
        'Api\V1\Public\ManuscriptController::uploadPayment'
    );

    $routes->get(
        'manuscripts/published',
        'Api\V1\Public\ManuscriptController::published'
    );

    $routes->get(
        'manuscripts/published/(:segment)',
        'Api\V1\Public\ManuscriptController::publishedDetails/$1'
    );
});
