<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/problems/create', [ProblemController::class, 'create'])->name('problems.create');

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/list', [ProjectController::class, 'list'])->name('projects.list');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/bulk', [ProjectController::class, 'bulkStore'])->name('projects.bulk');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/bulk', [ProjectController::class, 'bulkDestroy']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/admin/projects', function () {
        return view('admin.project.index');
    })->name('admin.projects.index');
    Route::get('/admin/projects/table', function () {
        return view('admin.project.table');
    })->name('admin.projects.table');

    Route::get('/admin/kanbans', [KanbanController::class, 'index'])->name('admin.kanbans.index');
    Route::get('/admin/kanbans/table', [KanbanController::class, 'table'])->name('admin.kanbans.table');
    Route::get('/kanbans/list', [KanbanController::class, 'list'])->name('kanbans.list');
    Route::post('/kanbans', [KanbanController::class, 'store'])->name('kanbans.store');
    Route::post('/kanbans/bulk', [KanbanController::class, 'bulkStore'])->name('kanbans.bulk');
    Route::put('/kanbans/{id}', [KanbanController::class, 'update'])->name('kanbans.update');
    Route::delete('/kanbans/{id}', [KanbanController::class, 'destroy'])->name('kanbans.destroy');

    Route::get('/admin/locations', [LocationController::class, 'index'])->name('admin.locations.index');
    Route::get('/admin/locations/table', [LocationController::class, 'table'])->name('admin.locations.table');
    Route::get('/locations/list', [LocationController::class, 'list'])->name('locations.list');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::post('/locations/bulk', [LocationController::class, 'bulkStore'])->name('locations.bulk');
    Route::put('/locations/{id}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{id}', [LocationController::class, 'destroy'])->name('locations.destroy');

    Route::get('/admin/items', [ItemController::class, 'index'])->name('admin.items.index');
    Route::get('/admin/items/table', [ItemController::class, 'table'])->name('admin.items.table');
    Route::get('/items/list', [ItemController::class, 'list'])->name('items.list');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::post('/items/bulk', [ItemController::class, 'bulkStore'])->name('items.bulk');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/bulk', [ItemController::class, 'bulkDestroy']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('/admin/users', [UserAdminController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/table', [UserAdminController::class, 'table'])->name('admin.users.table');
    Route::get('/users/list', [UserAdminController::class, 'list'])->name('users.list');
    Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserAdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserAdminController::class, 'destroy'])->name('users.destroy');

    Route::get('/admin/problems', [ProblemController::class, 'index'])->name('admin.problems.index');
    Route::get('/admin/problems/table', [ProblemController::class, 'table'])->name('admin.problems.table');
    Route::get('/problems/list', [ProblemController::class, 'list'])->name('problems.list');
    Route::post('/problems/store', [ProblemController::class, 'store'])->name('problems.store');
    Route::put('/problems/{id}', [ProblemController::class, 'update'])->name('problems.update');
    Route::get('/problems/export-list', [ProblemController::class, 'exportList'])->name('problems.export_list');
    Route::get('/problems/{id}/export', [ProblemController::class, 'export'])->name('problems.export');
    Route::post('/update-status/{id}', [ProblemController::class, 'updateStatus']);
    Route::delete('/problems/{id}', [ProblemController::class, 'destroy'])->name('problems.destroy');
});
