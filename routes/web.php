<?php

use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InspectionController as AdminInspectionController;
use App\Http\Controllers\Reviewer\InspectionController as ReviewerInspectionController;
use App\Http\Controllers\Technician\CaptureController as TechnicianCaptureController;
use App\Http\Controllers\Technician\DashboardController as TechnicianDashboardController;
use App\Http\Controllers\Technician\JobController as TechnicianJobController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Master\BuildingController;
use App\Http\Controllers\Admin\Master\ClientController;
use App\Http\Controllers\Admin\Master\MasterLookupController;
use App\Http\Controllers\Admin\Master\DataTypeController;
use App\Http\Controllers\Admin\Master\SectionController;
use App\Http\Controllers\Admin\Master\HierarchyController;
use App\Http\Controllers\Admin\Master\SiteController;
use App\Http\Controllers\Admin\MenuElementController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\QuestionnaireController;
use App\Http\Controllers\Admin\RbacController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\SiteController as ClientSiteController;
use App\Http\Controllers\Client\AssetController as ClientAssetController;
use App\Http\Controllers\Client\ReportController as ClientReportController;
use App\Http\Controllers\Client\FeedbackController as ClientFeedbackController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\ChangePasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Force password change — accessible to any authenticated user
Route::middleware('auth')->group(function () {
    Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');
});

// System Administrator — dashboard and questionnaires (admin-only)
Route::middleware(['auth', 'role:system-administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Menu Elements
    Route::get('/menu-elements', [MenuElementController::class, 'index'])->name('menu.index');
    Route::post('/menu-elements/{item}/move-up', [MenuElementController::class, 'moveUp'])->name('menu.moveUp');
    Route::post('/menu-elements/{item}/move-down', [MenuElementController::class, 'moveDown'])->name('menu.moveDown');
    Route::put('/menu-elements/{item}', [MenuElementController::class, 'update'])->name('menu.update');
    Route::delete('/menu-elements/{item}', [MenuElementController::class, 'destroy'])->name('menu.destroy');

    // Questionnaires — action-level permissions
    Route::prefix('questionnaires')->name('questionnaires.')->group(function () {
        Route::get('/',                   [QuestionnaireController::class, 'index'])       ->name('index')          ->middleware('permission:view questionnaires');
        Route::get('/create',             [QuestionnaireController::class, 'create'])      ->name('create')         ->middleware('permission:add questionnaires');
        Route::post('/',                  [QuestionnaireController::class, 'store'])       ->name('store')          ->middleware('permission:add questionnaires');
        Route::get('/{questionnaire}/edit',   [QuestionnaireController::class, 'edit'])   ->name('edit')           ->middleware('permission:edit questionnaires');
        Route::put('/{questionnaire}',        [QuestionnaireController::class, 'update']) ->name('update')         ->middleware('permission:edit questionnaires');
        Route::post('/{parent}/sub-group',    [QuestionnaireController::class, 'updateSubGroup'])->name('sub-group.update')->middleware('permission:edit questionnaires');
        Route::post('/{questionnaire}/move-up',   [QuestionnaireController::class, 'moveUp'])  ->name('move-up')   ->middleware('permission:edit questionnaires');
        Route::post('/{questionnaire}/move-down', [QuestionnaireController::class, 'moveDown'])->name('move-down') ->middleware('permission:edit questionnaires');
        Route::delete('/{questionnaire}', [QuestionnaireController::class, 'destroy'])    ->name('destroy')        ->middleware('permission:delete questionnaires');
    });
});

// Master Data — action-level permissions per section
Route::middleware('auth')->prefix('admin/master')->name('admin.master.')->group(function () {
    Route::get('/clients',              [ClientController::class, 'index'])  ->name('clients.index')   ->middleware('permission:view clients');
    Route::post('/clients',             [ClientController::class, 'store'])  ->name('clients.store')   ->middleware('permission:add clients');
    Route::put('/clients/{client}',     [ClientController::class, 'update']) ->name('clients.update')  ->middleware('permission:edit clients');
    Route::delete('/clients/{client}',  [ClientController::class, 'destroy'])->name('clients.destroy') ->middleware('permission:delete clients');

    Route::get('/sites',                [SiteController::class, 'index'])  ->name('sites.index')   ->middleware('permission:view sites');
    Route::post('/sites',               [SiteController::class, 'store'])  ->name('sites.store')   ->middleware('permission:add sites');
    Route::put('/sites/{site}',         [SiteController::class, 'update']) ->name('sites.update')  ->middleware('permission:edit sites');
    Route::delete('/sites/{site}',      [SiteController::class, 'destroy'])->name('sites.destroy') ->middleware('permission:delete sites');

    Route::get('/buildings',              [BuildingController::class, 'index'])  ->name('buildings.index')   ->middleware('permission:view buildings');
    Route::post('/buildings',             [BuildingController::class, 'store'])  ->name('buildings.store')   ->middleware('permission:add buildings');
    Route::put('/buildings/{building}',   [BuildingController::class, 'update']) ->name('buildings.update')  ->middleware('permission:edit buildings');
    Route::delete('/buildings/{building}',[BuildingController::class, 'destroy'])->name('buildings.destroy') ->middleware('permission:delete buildings');

    Route::get('/lookups',              [MasterLookupController::class, 'index'])  ->name('lookups.index')   ->middleware('permission:view reference data');
    Route::post('/lookups',             [MasterLookupController::class, 'store'])  ->name('lookups.store')   ->middleware('permission:add reference data');
    Route::put('/lookups/{lookup}',     [MasterLookupController::class, 'update']) ->name('lookups.update')  ->middleware('permission:edit reference data');
    Route::delete('/lookups/{lookup}',  [MasterLookupController::class, 'destroy'])->name('lookups.destroy') ->middleware('permission:delete reference data');
    Route::post('/lookups/{lookup}/reorder', [MasterLookupController::class, 'reorder'])->name('lookups.reorder')->middleware('permission:edit reference data');

    Route::get('/sections',             [SectionController::class, 'index'])  ->name('sections.index')   ->middleware('permission:view sections');
    Route::post('/sections',            [SectionController::class, 'store'])  ->name('sections.store')   ->middleware('permission:add sections');
    Route::put('/sections/{section}',   [SectionController::class, 'update']) ->name('sections.update')  ->middleware('permission:edit sections');
    Route::delete('/sections/{section}',[SectionController::class, 'destroy'])->name('sections.destroy') ->middleware('permission:delete sections');

    Route::get('/data-types',               [DataTypeController::class, 'index'])  ->name('data-types.index')   ->middleware('permission:view data types');
    Route::post('/data-types',              [DataTypeController::class, 'store'])  ->name('data-types.store')   ->middleware('permission:add data types');
    Route::put('/data-types/{fieldType}',   [DataTypeController::class, 'update']) ->name('data-types.update')  ->middleware('permission:edit data types');
    Route::delete('/data-types/{fieldType}',[DataTypeController::class, 'destroy'])->name('data-types.destroy') ->middleware('permission:delete data types');

    Route::get('/hierarchy',            [HierarchyController::class, 'index']) ->name('hierarchy.index')  ->middleware('permission:view client assignments');
    Route::put('/hierarchy/{client}',   [HierarchyController::class, 'update'])->name('hierarchy.update') ->middleware('permission:edit client assignments');
});

// System Settings — locked to system-administrator and manager only, with per-permission granularity
Route::middleware(['auth', 'role:system-administrator|manager'])->prefix('admin')->name('admin.')->group(function () {
    // Users — action-level permissions
    Route::get('/users',                    [RegisteredUserController::class, 'index'])          ->name('users.index')            ->middleware('permission:view users');
    Route::get('/users/create',             [RegisteredUserController::class, 'create'])         ->name('users.create')           ->middleware('permission:add users');
    Route::post('/users',                   [RegisteredUserController::class, 'store'])          ->name('users.store')            ->middleware('permission:add users');
    Route::get('/users/{user}/edit',        [RegisteredUserController::class, 'edit'])           ->name('users.edit')             ->middleware('permission:edit users');
    Route::put('/users/{user}',             [RegisteredUserController::class, 'update'])         ->name('users.update')           ->middleware('permission:edit users');
    Route::delete('/users/{user}',          [RegisteredUserController::class, 'destroy'])        ->name('users.destroy')          ->middleware('permission:delete users');
    Route::patch('/users/{user}/restore',   [RegisteredUserController::class, 'restore'])        ->name('users.restore')          ->middleware('permission:edit users')->withTrashed();
    Route::post('/users/{user}/send-credentials', [RegisteredUserController::class, 'sendCredentials'])->name('users.send-credentials')->middleware('permission:edit users');
    Route::put('/users/{user}/reset-password',   [RegisteredUserController::class, 'resetPassword'])  ->name('users.reset-password')  ->middleware('permission:edit users');

    // Roles (RBAC matrix)
    Route::get('/rbac',  [RbacController::class, 'index']) ->name('rbac.index')  ->middleware('permission:view roles');
    Route::put('/rbac',  [RbacController::class, 'update'])->name('rbac.update') ->middleware('permission:edit roles');

    // Permissions — action-level
    Route::get('/permissions',                    [PermissionController::class, 'index'])  ->name('permissions.index')  ->middleware('permission:view permissions');
    Route::post('/permissions',                   [PermissionController::class, 'store'])  ->name('permissions.store')  ->middleware('permission:add permissions');
    Route::put('/permissions/{permission}',       [PermissionController::class, 'update']) ->name('permissions.update') ->middleware('permission:edit permissions');
    Route::delete('/permissions/{permission}',    [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:delete permissions');

    // Audit Log
    Route::middleware('permission:view audit log')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });

    // Client Feedback
    Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
});

// Jobs — permission-based (managers and admins who have view/manage jobs)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('jobs')->name('jobs.')->group(function () {
        // Static routes must come before parameterised /{job} routes
        Route::get('/',       [JobController::class, 'index'])->middleware('permission:view jobs|manage jobs')->name('index');
        Route::get('/create', [JobController::class, 'create'])->middleware('permission:manage jobs')->name('create');
        Route::post('/',      [JobController::class, 'store'])->middleware('permission:manage jobs')->name('store');

        Route::get('/{job}',             [JobController::class, 'show'])->middleware('permission:view jobs|manage jobs')->name('show');
        Route::get('/{job}/edit',        [JobController::class, 'edit'])->middleware('permission:manage jobs')->name('edit');
        Route::put('/{job}',             [JobController::class, 'update'])->middleware('permission:manage jobs')->name('update');
        Route::get('/{job}/certificate',              [JobController::class, 'certificate'])->middleware('permission:view jobs|manage jobs')->name('certificate');
        Route::post('/{job}/send-certificate',        [JobController::class, 'sendCertificate'])->middleware('permission:manage jobs')->name('sendCertificate');
        Route::post('/{job}/toggle-certificate-access', [JobController::class, 'toggleCertificateAccess'])->middleware('permission:manage jobs')->name('toggleCertificateAccess');
    });
});

// Asset Register — permission-based (any role that has view/manage assets permission)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('assets')->name('assets.')->group(function () {
        // Static routes must come before parameterised /{asset} routes
        Route::get('/',         [AssetController::class, 'index'])->middleware('permission:view assets|manage assets')->name('index');
        Route::get('/history',  [AssetController::class, 'history'])->middleware('permission:view assets|manage assets')->name('history');
        Route::get('/create',   [AssetController::class, 'create'])->middleware('permission:manage assets')->name('create');
        Route::post('/',        [AssetController::class, 'store'])->middleware('permission:manage assets')->name('store');

        Route::get('/{asset}',                                [AssetController::class, 'show'])->middleware('permission:view assets|manage assets')->name('show');
        Route::get('/{asset}/edit',                           [AssetController::class, 'edit'])->middleware('permission:manage assets')->name('edit');
        Route::match(['put', 'patch'], '/{asset}',            [AssetController::class, 'update'])->middleware('permission:manage assets')->name('update');
        Route::patch('/{asset}/remove',      [AssetController::class, 'remove'])->middleware('permission:manage assets')->name('remove');
        Route::patch('/{asset}/reinstate',   [AssetController::class, 'reinstate'])->middleware('permission:manage assets')->name('reinstate');
        Route::patch('/{asset}/not-located', [AssetController::class, 'notLocated'])->middleware('permission:manage assets')->name('not-located');
    });
});

// Inspections — admin view (all records, no client scope)
Route::middleware(['auth', 'permission:review inspections'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/',            [AdminInspectionController::class, 'index'])->name('index');
        Route::get('/{inspection}', [AdminInspectionController::class, 'show'])->name('show');
    });
});

// Reviewer / Approver (Manager)
Route::middleware(['auth', 'role:manager'])->prefix('reviewer')->name('reviewer.')->group(function () {
    Route::get('/dashboard', function () {
        $clientIds = \App\Models\Client::where('manager_id', auth()->id())->pluck('id');
        $scope = fn ($q) => $clientIds->isEmpty() ? $q
            : $q->whereHas('asset.site', fn ($s) => $s->whereIn('client_id', $clientIds));

        $pendingCount       = $scope(\App\Models\InspectionRecord::query())->where('document_status', 'submitted')->count();
        $approvedTodayIds   = \Spatie\Activitylog\Models\Activity::where('log_name', 'inspection_record')
            ->where('description', 'updated')
            ->whereJsonContains('properties->attributes->document_status', 'approved')
            ->whereDate('created_at', today())
            ->pluck('subject_id');
        $approvedToday = $scope(\App\Models\InspectionRecord::query())
            ->whereIn('id', $approvedTodayIds)->count();
        $openJobs = \App\Models\Job::when($clientIds->isNotEmpty(), fn ($q) => $q->whereIn('client_id', $clientIds))
            ->whereNotIn('status', ['approved', 'issued', 'closed'])->count();
        return view('reviewer.dashboard', compact('pendingCount', 'approvedToday', 'openJobs'));
    })->name('dashboard');

    Route::get('/inspections',                           [ReviewerInspectionController::class, 'index'])->name('inspections.index');
    Route::get('/inspections/{inspection}',              [ReviewerInspectionController::class, 'show'])->name('inspections.show');
    Route::post('/inspections/{inspection}/approve',     [ReviewerInspectionController::class, 'approve'])->name('inspections.approve');
    Route::post('/inspections/{inspection}/reject',      [ReviewerInspectionController::class, 'reject'])->name('inspections.reject');
});

// Field Technician
Route::middleware(['auth', 'role:field-technician'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', [TechnicianDashboardController::class, 'index'])->name('dashboard');

    Route::get('/jobs',          [TechnicianJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}',    [TechnicianJobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{job}/submit-for-review', [TechnicianJobController::class, 'submitForReview'])->name('jobs.submitForReview');
    Route::post('/jobs/{job}/assets',           [TechnicianJobController::class, 'storeAsset'])->name('jobs.assets.store');
    Route::put('/jobs/{job}/assets/{asset}',    [TechnicianJobController::class, 'updateAsset'])->name('jobs.assets.update');
    Route::delete('/jobs/{job}/assets/{asset}', [TechnicianJobController::class, 'destroyAsset'])->name('jobs.assets.destroy');

    Route::get('/jobs/{job}/inspect',  [TechnicianCaptureController::class, 'inspectForm'])->name('jobs.inspect');
    Route::post('/jobs/{job}/inspect', [TechnicianCaptureController::class, 'inspectStore'])->name('jobs.inspect.store');

    Route::get('/jobs/{job}/install',  [TechnicianCaptureController::class, 'installForm'])->name('jobs.install');
    Route::post('/jobs/{job}/install', [TechnicianCaptureController::class, 'installStore'])->name('jobs.install.store');

    Route::get('/jobs/{job}/register-inspect',  [TechnicianCaptureController::class, 'registerInspectForm'])->name('jobs.register-inspect');
    Route::post('/jobs/{job}/register-inspect', [TechnicianCaptureController::class, 'registerInspectStore'])->name('jobs.register-inspect.store');
    Route::get('/jobs/{job}/check-asset-codes', [TechnicianCaptureController::class, 'checkAssetCodes'])->name('jobs.check-asset-codes');
});

// Client User
Route::middleware(['auth', 'role:client-user'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard',              [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/sites',                  [ClientSiteController::class,      'index'])->name('sites.index');
    Route::get('/assets',                 [ClientAssetController::class,     'index'])->name('assets.index');
    Route::get('/assets/{asset}',         [ClientAssetController::class,     'show'])->name('assets.show');
    Route::get('/reports',                [ClientReportController::class,    'index'])->name('reports.index');
    Route::get('/certificates/{job}',     [JobController::class, 'certificate'])->name('certificates.download');
    Route::get('/feedback',               [ClientFeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback',              [ClientFeedbackController::class, 'store'])->name('feedback.store');
});

// Notification actions (all authenticated users)
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::post('/{id}/read', function (string $id) {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        return back();
    })->name('markRead');

    Route::post('/read-all', function () {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return back();
    })->name('markAllRead');
});

// Shared authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        return match (true) {
            $user->hasRole('system-administrator') => redirect()->route('admin.dashboard'),
            $user->hasRole('manager')              => redirect()->route('reviewer.dashboard'),
            $user->hasRole('field-technician')     => redirect()->route('technician.dashboard'),
            $user->hasRole('client-user')          => redirect()->route('client.dashboard'),
            default                                => redirect('/'),
        };
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
