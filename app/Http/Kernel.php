<?php

namespace App\Http;

use App\Http\Middleware\Admin;
use App\Http\Middleware\AdminSuperAdmin;
use App\Http\Middleware\AdmissionOfficer;
use App\Http\Middleware\ApiAdmin;
use App\Http\Middleware\ApiLibrarian;
use App\Http\Middleware\ApiParents;
use App\Http\Middleware\ApiStudent;
use App\Http\Middleware\ApiTeacher;
use App\Http\Middleware\Applicant;
use App\Http\Middleware\Authorized;
use App\Http\Middleware\DesktopAdmin;
use App\Http\Middleware\Doorman;
use App\Http\Middleware\Employee;
use App\Http\Middleware\HasAnyRole;
use App\Http\Middleware\HasAnyRoleApi;
use App\Http\Middleware\KitchenAdmin;
use App\Http\Middleware\KitchenStaff;
use App\Http\Middleware\Librarian;
use App\Http\Middleware\Parents;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SentinelAuth;
use App\Http\Middleware\Student;
use App\Http\Middleware\SuperAdmin;
use App\Http\Middleware\Supplier;
use App\Http\Middleware\Teacher;
use App\Http\Middleware\XSSProtection;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // appending custom middleware
        \App\Http\Middleware\HttpsProtocol::class,
        \App\Http\Middleware\FrameHeadersMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            SubstituteBindings::class,
            \App\Http\Middleware\Locale::class,
        ],

        'api' => [
            EnsureFrontendRequestsAreStateful::class,
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'authorized' => Authorized::class,
        'super_admin' => SuperAdmin::class,
        'admin_super_admin' => AdminSuperAdmin::class,
        'admin' => Admin::class,
        'teacher' => Teacher::class,
        'student' => Student::class,
        'employee' => Employee::class,
        'parent' => Parents::class,
        'librarian' => Librarian::class,
        'supplier' => Supplier::class,
        'kitchen_admin' => KitchenAdmin::class,
        'kitchen_staff' => KitchenStaff::class,
        'doorman' => Doorman::class,
        'admission_officer' => AdmissionOfficer::class,
        'applicant' => Applicant::class,
        'sentinel' => SentinelAuth::class,
        'has_any_role' => HasAnyRole::class,
        'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
        'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
        'api.teacher' => ApiTeacher::class,
        'api.student' => ApiStudent::class,
        'api.parent' => ApiParents::class,
        'api.librarian' => ApiLibrarian::class,
        'api.admin' => ApiAdmin::class,
        'has_any_role.api' => HasAnyRoleApi::class,
        'xss_protection' => XSSProtection::class,
        'desktop.admin' => DesktopAdmin::class,
    ];
}
