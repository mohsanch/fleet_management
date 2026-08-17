<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\FleetDailyDataController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\StoreItemController;
use App\Http\Controllers\DriverSalaryController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\PasgiAdvanceController;
use App\Http\Controllers\EmployeeAdvanceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ReportController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard Routes (Protected)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User & People Management
    Route::resource('users', UserController::class)->middleware('can:users.view');
    Route::resource('drivers', DriverController::class)->middleware('can:manage-drivers');
    Route::resource('employees', EmployeeController::class)->middleware('can:manage-employees');

    // Fleet Management
    Route::resource('categories', CategoryController::class)->middleware('can:manage-settings');
    Route::resource('vehicles', VehicleController::class)->middleware('can:vehicles.view');
    Route::resource('daily-data', FleetDailyDataController::class)->parameters(['daily-data' => 'dailyLog'])->middleware('can:daily-data.view');

    // Financials
    Route::resource('incomes', IncomeController::class)->middleware('can:finance.view');
    Route::resource('expenses', ExpenseController::class)->middleware('can:finance.view');
    Route::resource('maintenances', MaintenanceController::class)->middleware('can:maintenance.view');
    Route::resource('store-items', StoreItemController::class)->middleware('can:store.view');

    // Payroll & Advances
    Route::resource('driver-salaries', DriverSalaryController::class)->middleware('can:payroll.view');
    Route::resource('employee-salaries', EmployeeSalaryController::class)->middleware('can:payroll.view');
    Route::resource('pasgi-advances', PasgiAdvanceController::class)->middleware('can:advances.view');
    Route::get('/pasgi-advances/driver/{driver}/balance', [PasgiAdvanceController::class, 'driverBalance'])->name('pasgi-advances.driver-balance')->middleware('can:advances.view');
    Route::post('/pasgi-advances/store-adjustment', [PasgiAdvanceController::class, 'storeAdjustment'])->name('pasgi-advances.store-adjustment')->middleware('can:advances.view');
    Route::resource('employee-advances', EmployeeAdvanceController::class)->middleware('can:advances.view');

    // Settings & Logs
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('can:settings.view');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update')->middleware('can:settings.edit');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('can:activity-logs.view');

    // Profile Settings
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Attachments
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('can:reports.view');
    Route::get('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate')->middleware('can:reports.view');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf')->middleware('can:reports.export');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel')->middleware('can:reports.export');

    // Super Admin Panel — full control over roles, permissions, users, logs
    Route::middleware('can:super-admin-only')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/',                                   [\App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');

        // Roles
        Route::get('/roles',                              [\App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create',                       [\App\Http\Controllers\RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles',                             [\App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit',                  [\App\Http\Controllers\RoleController::class, 'edit'])->name('roles.edit');
        Route::patch('/roles/{role}/rename',              [\App\Http\Controllers\RoleController::class, 'rename'])->name('roles.rename');
        Route::put('/roles/{role}',                       [\App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}',                    [\App\Http\Controllers\RoleController::class, 'destroy'])->name('roles.destroy');

        // Permissions
        Route::get('/permissions',                        [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions',                       [\App\Http\Controllers\PermissionController::class, 'store'])->name('permissions.store');
        Route::post('/permissions/bulk',                  [\App\Http\Controllers\PermissionController::class, 'bulk'])->name('permissions.bulk');
        Route::delete('/permissions/{permission}',        [\App\Http\Controllers\PermissionController::class, 'destroy'])->name('permissions.destroy');

        // Users management
        Route::get('/users',                              [\App\Http\Controllers\SuperAdminController::class, 'users'])->name('users.index');
        Route::post('/users/{user}/toggle',               [\App\Http\Controllers\SuperAdminController::class, 'toggleUser'])->name('users.toggle');
        Route::post('/users/{user}/reset-password',       [\App\Http\Controllers\SuperAdminController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/direct-permissions',   [\App\Http\Controllers\SuperAdminController::class, 'updateDirectPermissions'])->name('users.direct-permissions');

        // Activity logs
        Route::get('/logs',                               [\App\Http\Controllers\SuperAdminController::class, 'logs'])->name('logs.index');
    });
});
