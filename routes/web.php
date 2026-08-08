<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Master\BuildingController;
use App\Http\Controllers\Admin\Master\ClientController;
use App\Http\Controllers\Admin\Master\MasterLookupController;
use App\Http\Controllers\Admin\Master\DataTypeController;
use App\Http\Controllers\Admin\Master\QuestionnaireController;
use App\Http\Controllers\Admin\Master\SectionController;
use App\Http\Controllers\Admin\Master\SiteController;
use App\Http\Controllers\Admin\PermissionController;
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

        Route::get('/questionnaires', [QuestionnaireController::class, 'index'])->name('questionnaires.index');
        Route::post('/questionnaires', [QuestionnaireController::class, 'store'])->name('questionnaires.store');
        Route::get('/questionnaires/create', [QuestionnaireController::class, 'create'])->name('questionnaires.create');
        Route::get('/questionnaires/{questionnaire}/edit', [QuestionnaireController::class, 'edit'])->name('questionnaires.edit');
        Route::get('/questionnaires/{parent}/sub-group', [QuestionnaireController::class, 'subGroupData'])->name('questionnaires.sub-group.data');
        Route::post('/questionnaires/{parent}/sub-group', [QuestionnaireController::class, 'updateSubGroup'])->name('questionnaires.sub-group.update');
        Route::put('/questionnaires/{questionnaire}', [QuestionnaireController::class, 'update'])->name('questionnaires.update');
        Route::delete('/questionnaires/{questionnaire}', [QuestionnaireController::class, 'destroy'])->name('questionnaires.destroy');
    });

    Route::get('/rbac', [RbacController::class, 'index'])->name('rbac.index');
    Route::put('/rbac', [RbacController::class, 'update'])->name('rbac.update');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
});

// Reviewer / Approver (Manager)
Route::middleware(['auth', 'role:manager'])->prefix('reviewer')->name('reviewer.')->group(function () {
    Route::get('/dashboard', fn () => view('reviewer.dashboard'))->name('dashboard');
});

// Field Technician
Route::middleware(['auth', 'role:field-technician'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', fn () => view('technician.dashboard'))->name('dashboard');
});

// Client User
Route::middleware(['auth', 'role:client-user'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', fn () => view('client.dashboard'))->name('dashboard');
});

// Shared authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
