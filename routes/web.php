<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/table', [ProjectController::class, 'table'])->name('projects.table');
        Route::get('/list', [ProjectController::class, 'list'])->name('projects.list');
        Route::middleware('can:admin')->group(function () {
            Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
            Route::post('/bulk', [ProjectController::class, 'bulkStore'])->name('projects.bulk');
            Route::put('/{id}', [ProjectController::class, 'update'])->name('projects.update');
            Route::delete('/bulk', [ProjectController::class, 'bulkDestroy']);
            Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        });
    });

    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('items.index');
        Route::get('/table', [ItemController::class, 'table'])->name('items.table');
        Route::get('/list', [ItemController::class, 'list'])->name('items.list');
        Route::middleware('can:admin')->group(function () {
            Route::post('/', [ItemController::class, 'store'])->name('items.store');
            Route::post('/bulk', [ItemController::class, 'bulkStore'])->name('items.bulk');
            Route::put('/{id}', [ItemController::class, 'update'])->name('items.update');
            Route::delete('/bulk', [ItemController::class, 'bulkDestroy']);
            Route::delete('/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
        });
    });

    Route::prefix('kanbans')->group(function () {
        Route::get('/', [KanbanController::class, 'index'])->name('kanbans.index');
        Route::get('/table', [KanbanController::class, 'table'])->name('kanbans.table');
        Route::get('/list', [KanbanController::class, 'list'])->name('kanbans.list');
        Route::middleware('can:admin')->group(function () {
            Route::post('/', [KanbanController::class, 'store'])->name('kanbans.store');
            Route::post('/bulk', [KanbanController::class, 'bulkStore'])->name('kanbans.bulk');
            Route::put('/{id}', [KanbanController::class, 'update'])->name('kanbans.update');
            Route::delete('/{id}', [KanbanController::class, 'destroy'])->name('kanbans.destroy');
            Route::delete('/bulk', [KanbanController::class, 'bulkDestroy'])->name('kanbans.bulk.destroy');
        });
    });

    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('locations.index');
        Route::get('/table', [LocationController::class, 'table'])->name('locations.table');
        Route::get('/list', [LocationController::class, 'list'])->name('locations.list');
        Route::middleware('can:admin')->group(function () {
            Route::post('/', [LocationController::class, 'store'])->name('locations.store');
            Route::post('/bulk', [LocationController::class, 'bulkStore'])->name('locations.bulk');
            Route::put('/{id}', [LocationController::class, 'update'])->name('locations.update');
            Route::delete('/{id}', [LocationController::class, 'destroy'])->name('locations.destroy');
            Route::delete('/bulk', [LocationController::class, 'bulkDestroy'])->name('locations.bulk.destroy');
        });
    });

    Route::prefix('machines')->group(function () {
        Route::get('/', [MachineController::class, 'index'])->name('machines.index');
        Route::get('/list', [MachineController::class, 'list'])->name('machines.list');
        Route::middleware('can:admin')->group(function () {
            Route::post('/store', [MachineController::class, 'store'])->name('machines.store');
            Route::put('/{id}', [MachineController::class, 'update'])->name('machines.update');
            Route::delete('/{id}', [MachineController::class, 'destroy'])->name('machines.destroy');
            Route::delete('/bulk', [MachineController::class, 'bulkDestroy'])->name('machines.bulk.destroy');
        });
    });

    Route::prefix('users')->middleware('can:admin')->group(function () {
        Route::get('/', [UserAdminController::class, 'index'])->name('users.index');
        Route::get('/table', [UserAdminController::class, 'table'])->name('users.table');
        Route::get('/list', [UserAdminController::class, 'list'])->name('users.list');
        Route::post('/', [UserAdminController::class, 'store'])->name('users.store');
        Route::put('/{id}', [UserAdminController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserAdminController::class, 'destroy'])->name('users.destroy');
        Route::delete('/bulk', [UserAdminController::class, 'bulkDestroy'])->name('users.bulk.destroy');
    });

    Route::prefix('problems')->group(function () {
        Route::get('/', [ProblemController::class, 'index'])->name('problems.index');
        Route::get('/table', [ProblemController::class, 'table'])->name('problems.table');
        Route::get('/gallery', [ProblemController::class, 'gallery'])->name('problems.gallery');
        Route::get('/create', [ProblemController::class, 'create'])->name('problems.create');
        Route::get('/list', [ProblemController::class, 'list'])->name('problems.list');
        Route::post('/store', [ProblemController::class, 'store'])->name('problems.store');
        Route::put('/{id}', [ProblemController::class, 'update'])->name('problems.update');
        Route::delete('/{id}', [ProblemController::class, 'destroy'])->name('problems.destroy')->middleware('can:admin');
        Route::get('/export-group', [ProblemController::class, 'exportGroup'])->name('problems.export_group');
        Route::get('/export-list', [ProblemController::class, 'exportList'])->name('problems.export_list');
        Route::get('/{id}/export', [ProblemController::class, 'export'])->name('problems.export');
    });

    Route::get('/problem/export', [ProblemController::class, 'exportProblem'])->name('problem.export');
    Route::post('/update-status/{id}', [ProblemController::class, 'updateStatus'])->middleware('can:admin');
});
