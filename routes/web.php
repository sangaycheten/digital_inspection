<?php

use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Reviewer\InspectionController as ReviewerInspectionController;
use App\Http\Controllers\Technician\CaptureController as TechnicianCaptureController;
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
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\QuestionnaireController;
use App\Http\Controllers\Admin\RbacController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// System Administrator
Route::middleware(['auth', 'role:system-administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard', [
            'userCount' => \App\Models\User::count(),
        ]);
    })->name('dashboard');
    Route::get('/users', [RegisteredUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [RegisteredUserController::class, 'create'])->name('users.create');
    Route::post('/users', [RegisteredUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [RegisteredUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [RegisteredUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [RegisteredUserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/restore', [RegisteredUserController::class, 'restore'])->name('users.restore')->withTrashed();

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

        Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
        Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
        Route::put('/sites/{site}', [SiteController::class, 'update'])->name('sites.update');
        Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');

        Route::get('/buildings', [BuildingController::class, 'index'])->name('buildings.index');
        Route::post('/buildings', [BuildingController::class, 'store'])->name('buildings.store');
        Route::put('/buildings/{building}', [BuildingController::class, 'update'])->name('buildings.update');
        Route::delete('/buildings/{building}', [BuildingController::class, 'destroy'])->name('buildings.destroy');

        Route::get('/lookups', [MasterLookupController::class, 'index'])->name('lookups.index');
        Route::post('/lookups', [MasterLookupController::class, 'store'])->name('lookups.store');
        Route::put('/lookups/{lookup}', [MasterLookupController::class, 'update'])->name('lookups.update');
        Route::delete('/lookups/{lookup}', [MasterLookupController::class, 'destroy'])->name('lookups.destroy');

        Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
        Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

        Route::get('/data-types', [DataTypeController::class, 'index'])->name('data-types.index');
        Route::post('/data-types', [DataTypeController::class, 'store'])->name('data-types.store');
        Route::put('/data-types/{fieldType}', [DataTypeController::class, 'update'])->name('data-types.update');
        Route::delete('/data-types/{fieldType}', [DataTypeController::class, 'destroy'])->name('data-types.destroy');

        Route::get('/hierarchy', [HierarchyController::class, 'index'])->name('hierarchy.index');
        Route::put('/hierarchy/{client}', [HierarchyController::class, 'update'])->name('hierarchy.update');
    });

    // Questionnaires (standalone module)
    Route::prefix('questionnaires')->name('questionnaires.')->group(function () {
        Route::get('/', [QuestionnaireController::class, 'index'])->name('index');
        Route::post('/', [QuestionnaireController::class, 'store'])->name('store');
        Route::get('/create', [QuestionnaireController::class, 'create'])->name('create');
        Route::get('/{questionnaire}/edit', [QuestionnaireController::class, 'edit'])->name('edit');
        Route::post('/{parent}/sub-group', [QuestionnaireController::class, 'updateSubGroup'])->name('sub-group.update');
        Route::put('/{questionnaire}', [QuestionnaireController::class, 'update'])->name('update');
        Route::delete('/{questionnaire}', [QuestionnaireController::class, 'destroy'])->name('destroy');
    });

    Route::get('/rbac', [RbacController::class, 'index'])->name('rbac.index');
    Route::put('/rbac', [RbacController::class, 'update'])->name('rbac.update');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    // Jobs
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/',              [JobController::class, 'index'])->name('index');
        Route::get('/create',       [JobController::class, 'create'])->name('create');
        Route::post('/',            [JobController::class, 'store'])->name('store');
        Route::get('/{job}',        [JobController::class, 'show'])->name('show');
        Route::get('/{job}/edit',   [JobController::class, 'edit'])->name('edit');
        Route::put('/{job}',        [JobController::class, 'update'])->name('update');
    });

    // Asset Register
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/',                    [AssetController::class, 'index'])->name('index');
        Route::get('/create',              [AssetController::class, 'create'])->name('create');
        Route::post('/',                   [AssetController::class, 'store'])->name('store');
        Route::get('/{asset}',             [AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/edit',        [AssetController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{asset}', [AssetController::class, 'update'])->name('update');
        Route::patch('/{asset}/remove',    [AssetController::class, 'remove'])->name('remove');
    });
});

// Reviewer / Approver (Manager)
Route::middleware(['auth', 'role:manager'])->prefix('reviewer')->name('reviewer.')->group(function () {
    Route::get('/dashboard', function () {
        $clientIds = \App\Models\Client::where('manager_id', auth()->id())->pluck('id');
        $scope = fn ($q) => $clientIds->isEmpty() ? $q
            : $q->whereHas('asset.site', fn ($s) => $s->whereIn('client_id', $clientIds));

        $pendingCount  = $scope(\App\Models\InspectionRecord::query())->where('document_status', 'draft')->count();
        $approvedToday = $scope(\App\Models\InspectionRecord::query())
            ->where('document_status', 'approved')->whereDate('created_at', today())->count();
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
    Route::get('/dashboard', fn () => view('technician.dashboard'))->name('dashboard');

    Route::get('/jobs',          [TechnicianJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}',    [TechnicianJobController::class, 'show'])->name('jobs.show');

    Route::get('/jobs/{job}/inspect',  [TechnicianCaptureController::class, 'inspectForm'])->name('jobs.inspect');
    Route::post('/jobs/{job}/inspect', [TechnicianCaptureController::class, 'inspectStore'])->name('jobs.inspect.store');

    Route::get('/jobs/{job}/install',  [TechnicianCaptureController::class, 'installForm'])->name('jobs.install');
    Route::post('/jobs/{job}/install', [TechnicianCaptureController::class, 'installStore'])->name('jobs.install.store');
});

// Client User
Route::middleware(['auth', 'role:client-user'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', fn () => view('client.dashboard'))->name('dashboard');
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
