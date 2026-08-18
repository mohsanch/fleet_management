<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\DriverVehicleAssignment;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Maintenance;
use App\Models\StoreItem;
use App\Models\FleetDailyData;
use App\Models\PasgiAdvance;
use App\Models\PasgiAdjustment;
use App\Models\DriverSalary;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAdjustment;
use App\Models\EmployeeSalary;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles & Permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole      = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $managerRole    = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $staffRole      = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $userRole       = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $accountantRole = Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'web']);
        $dataEntryRole  = Role::firstOrCreate(['name' => 'Data Entry', 'guard_name' => 'web']);
        $viewerRole     = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);

        // Legacy Permissions
        $legacyPermissions = [
            'manage-users',
            'manage-settings',
            'view-financials',
            'add-transactions',
            'edit-transactions',
            'delete-transactions',
            'manage-employees',
            'manage-drivers',
        ];

        foreach ($legacyPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Module-based Permissions
        $modules = [
            'users'        => ['view', 'create', 'edit', 'delete'],
            'roles'        => ['view', 'create', 'edit', 'delete'],
            'permissions'  => ['view', 'create', 'edit', 'delete'],
            'vehicles'     => ['view', 'create', 'edit', 'delete', 'export'],
            'daily-data'   => ['view', 'create', 'edit', 'delete', 'approve', 'export'],
            'maintenance'  => ['view', 'create', 'edit', 'delete', 'export'],
            'store'        => ['view', 'create', 'edit', 'delete', 'export'],
            'incomes'      => ['view', 'create', 'edit', 'delete'],
            'expenses'     => ['view', 'create', 'edit', 'delete'],
            'finance'      => ['view', 'create', 'edit', 'delete', 'export'],
            'payroll'      => ['view', 'create', 'edit', 'delete', 'export'],
            'advances'     => ['view', 'create', 'edit', 'delete', 'export'],
            'reports'      => ['view', 'export'],
            'settings'     => ['view', 'edit'],
            'activity-logs' => ['view']
        ];

        $allPermNames = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permName = "{$module}.{$action}";
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                $allPermNames[] = $permName;
            }
        }

        // Super Admin gets all permissions (Legacy + Module)
        $superAdminRole->syncPermissions(array_merge($legacyPermissions, $allPermNames));

        // Admin gets almost everything except permissions & roles management
        $adminPerms = array_filter($allPermNames, function($name) {
            return !str_starts_with($name, 'permissions.') && !str_starts_with($name, 'roles.');
        });
        $adminRole->syncPermissions(array_merge(
            ['manage-settings', 'view-financials', 'add-transactions', 'edit-transactions', 'delete-transactions', 'manage-employees', 'manage-drivers'],
            $adminPerms
        ));

        // Manager gets operational + viewing permissions, but no deletion or settings modifications
        $managerPerms = array_filter($allPermNames, function($name) {
            return !str_contains($name, '.delete') 
                && !str_starts_with($name, 'permissions.') 
                && !str_starts_with($name, 'roles.') 
                && !str_starts_with($name, 'settings.');
        });
        $managerRole->syncPermissions($managerPerms);

        // Staff / Data Entry gets view and create operations
        $staffPerms = array_filter($allPermNames, function($name) {
            return str_contains($name, '.view') || str_contains($name, '.create');
        });
        $staffRole->syncPermissions($staffPerms);

        // Accountant gets legacy financials, and expenses/finance/payroll/advances modules
        $accountantPerms = array_filter($allPermNames, function($name) {
            return str_starts_with($name, 'expenses.')
                || str_starts_with($name, 'finance.') 
                || str_starts_with($name, 'payroll.') 
                || str_starts_with($name, 'advances.') 
                || str_starts_with($name, 'reports.');
        });
        $accountantRole->syncPermissions(array_merge(
            ['view-financials', 'add-transactions', 'edit-transactions'],
            $accountantPerms
        ));

        // Data Entry gets legacy add transactions, and daily-data / vehicle / maintenance view/create/edit
        $dataEntryPerms = array_filter($allPermNames, function($name) {
            return (str_starts_with($name, 'daily-data.') || str_starts_with($name, 'vehicles.') || str_starts_with($name, 'maintenance.'))
                && (str_contains($name, '.view') || str_contains($name, '.create') || str_contains($name, '.edit'));
        });
        $dataEntryRole->syncPermissions(array_merge(
            ['add-transactions'],
            $dataEntryPerms
        ));

        // Viewer gets view-financials and all module view permissions
        $viewerPerms = array_filter($allPermNames, function($name) {
            return str_contains($name, '.view');
        });
        $viewerRole->syncPermissions(array_merge(
            ['view-financials'],
            $viewerPerms
        ));

        // 2. Seed Default Users
        $swlBranchId = Branch::where('code', 'SWL')->value('id');
        $skpBranchId = Branch::where('code', 'SKP')->value('id');

        // 2. Seed Custom Users List
        $usersToSeed = [
            [
                'name' => 'Mohsan',
                'email' => 'mohsan@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'super_admin',
                'branch_id' => null,
                'role' => $superAdminRole,
            ],
            [
                'name' => 'Parvaze',
                'email' => 'parvaze@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'admin',
                'branch_id' => null,
                'role' => $adminRole,
            ],
            [
                'name' => 'Mazhar',
                'email' => 'mazhar@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'admin',
                'branch_id' => null,
                'role' => $adminRole,
            ],
            [
                'name' => 'Ahmad',
                'email' => 'ahmad@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'accountant',
                'branch_id' => null,
                'role' => $accountantRole,
            ],
            [
                'name' => 'Allah Rakkha',
                'email' => 'allahrakkha@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'accountant',
                'branch_id' => null,
                'role' => $accountantRole,
            ],
            [
                'name' => 'Zeshan',
                'email' => 'zeshan@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'staff',
                'branch_id' => $swlBranchId,
                'role' => $staffRole,
            ],
            [
                'name' => 'Usama',
                'email' => 'usama@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'staff',
                'branch_id' => $skpBranchId,
                'role' => $staffRole,
            ],
            [
                'name' => 'Ali Hamza',
                'email' => 'alihamza@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'staff',
                'branch_id' => $skpBranchId,
                'role' => $staffRole,
            ],
            [
                'name' => 'Qasim',
                'email' => 'qasim@gmail.com',
                'password' => Hash::make('123456789'),
                'is_active' => true,
                'user_type' => 'staff',
                'branch_id' => $skpBranchId,
                'role' => $staffRole,
            ],
        ];

        foreach ($usersToSeed as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $user->assignRole($role);
        }

        // 3. Seed Settings
        Setting::updateOrCreate(['key' => 'fuel_price_per_liter'], ['value' => '272.50']);
        Setting::updateOrCreate(['key' => 'maintenance_interval_km'], ['value' => '5000']);
        Setting::updateOrCreate(['key' => 'lock_cutoff_days'], ['value' => '30']);

        // 4. Seed Categories
        $incomeCats = [
            'Freight Delivery' => Category::firstOrCreate(['name' => 'Freight Delivery', 'type' => 'income']),
            'Container Cargo' => Category::firstOrCreate(['name' => 'Container Cargo', 'type' => 'income']),
            'Retail Delivery' => Category::firstOrCreate(['name' => 'Retail Delivery', 'type' => 'income']),
            'Local Logistics' => Category::firstOrCreate(['name' => 'Local Logistics', 'type' => 'income']),
        ];

        $expenseCats = [
            'Staff Salary' => Category::firstOrCreate(['name' => 'Staff Salary', 'type' => 'expense']),
            'Diesel Retail' => Category::firstOrCreate(['name' => 'Diesel Retail', 'type' => 'expense']),
            'Store Purchases' => Category::firstOrCreate(['name' => 'Store Purchases', 'type' => 'expense']),
            'Office Expenses' => Category::firstOrCreate(['name' => 'Office Expenses', 'type' => 'expense']),
            'Other Expense' => Category::firstOrCreate(['name' => 'Other Expense', 'type' => 'expense']),
        ];
    }
}
