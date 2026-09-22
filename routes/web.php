<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GownController;
use App\Http\Controllers\AccessoryController;

use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;


Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Owner
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {

        Route::get('/dashboard', [
            OwnerDashboardController::class,
            'index'
        ])->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('gowns', GownController::class);
    Route::resource('accessories', AccessoryController::class);

    });



/*
|--------------------------------------------------------------------------
| Employee
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {

        Route::get('/dashboard', [
            EmployeeDashboardController::class,
            'index'
        ])->name('dashboard');

    });


/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

        Route::get('/dashboard', [
            CustomerDashboardController::class,
            'index'
        ])->name('dashboard');

    });


require __DIR__.'/auth.php';
