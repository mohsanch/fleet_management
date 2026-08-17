<?php

namespace Database\Seeders;

use App\Models\User;
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

        // Accountant gets legacy financials, and finance/payroll/advances modules
        $accountantPerms = array_filter($allPermNames, function($name) {
            return str_starts_with($name, 'finance.') 
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
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@fleet.local'],
            [
                'name' => 'Super Admin User',
                'password' => Hash::make('password'),
                'is_active' => true,
                'user_type' => 'super_admin'
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        $accountant = User::firstOrCreate(
            ['email' => 'accountant@fleet.local'],
            [
                'name' => 'Accountant User',
                'password' => Hash::make('password'),
                'is_active' => true,
                'user_type' => 'accountant'
            ]
        );
        $accountant->assignRole($accountantRole);

        $dataEntry = User::firstOrCreate(
            ['email' => 'dataentry@fleet.local'],
            [
                'name' => 'Data Entry User',
                'password' => Hash::make('password'),
                'is_active' => true,
                'user_type' => 'staff'
            ]
        );
        $dataEntry->assignRole($dataEntryRole);

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

        // 5. Seed Drivers
        $driversData = [
            ['name' => 'Sajid Khan', 'contact' => '0300-1234567', 'base_salary' => 45000, 'status' => 'active'],
            ['name' => 'Imran Ali', 'contact' => '0312-9876543', 'base_salary' => 40000, 'status' => 'active'],
            ['name' => 'Muhammad Bilal', 'contact' => '0333-5555555', 'base_salary' => 50000, 'status' => 'active'],
            ['name' => 'Waqas Ahmed', 'contact' => '0345-4444444', 'base_salary' => 42000, 'status' => 'active'],
            ['name' => 'Zahid Mehmood', 'contact' => '0321-7777777', 'base_salary' => 38000, 'status' => 'active'],
        ];

        $drivers = [];
        foreach ($driversData as $dData) {
            $drivers[] = Driver::firstOrCreate(['name' => $dData['name']], $dData);
        }

        // 6. Seed Vehicles
        $vehiclesData = [
            ['vehicle_number' => 'LHR-9842', 'registration_name' => 'Hino Truck', 'type' => 'Heavy Duty', 'assigned_driver_id' => $drivers[0]->id, 'status' => 'active'],
            ['vehicle_number' => 'KHI-1102', 'registration_name' => 'Isuzu D-Max', 'type' => 'Light Commercial', 'assigned_driver_id' => $drivers[1]->id, 'status' => 'active'],
            ['vehicle_number' => 'ISB-5421', 'registration_name' => 'Volvo FH16', 'type' => 'Trailer', 'assigned_driver_id' => $drivers[2]->id, 'status' => 'inactive'],
            ['vehicle_number' => 'PEW-7783', 'registration_name' => 'Foton Auman', 'type' => 'Heavy Cargo', 'assigned_driver_id' => $drivers[3]->id, 'status' => 'active'],
            ['vehicle_number' => 'MUL-3329', 'registration_name' => 'Hino Ranger', 'type' => 'Medium Duty', 'assigned_driver_id' => $drivers[4]->id, 'status' => 'active'],
        ];

        $vehicles = [];
        foreach ($vehiclesData as $vData) {
            $vehicles[] = Vehicle::firstOrCreate(['vehicle_number' => $vData['vehicle_number']], $vData);
        }

        // 7. Seed Driver Vehicle Assignment History
        foreach ($vehicles as $key => $vehicle) {
            DriverVehicleAssignment::firstOrCreate(
                [
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $vehicle->assigned_driver_id,
                    'assigned_from' => Carbon::now()->subMonths(3)->toDateString(),
                ],
                [
                    'assigned_to' => null
                ]
            );
        }

        // 8. Seed Incomes, Expenses, Maintenances, and Daily Data over last 30 days
        $startDate = Carbon::now()->subDays(30);

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $formattedDate = $date->toDateString();

            // Seed daily incomes on some days
            if ($i % 2 === 0) {
                Income::create([
                    'category_id' => $incomeCats['Freight Delivery']->id,
                    'amount' => rand(60000, 120000),
                    'date' => $formattedDate,
                    'description' => 'Completed delivery trip #' . rand(100, 999),
                    'reference_source' => 'Client Invoice #' . (5000 + $i),
                    'created_by' => $superAdmin->id
                ]);
            }
            if ($i % 3 === 0) {
                Income::create([
                    'category_id' => $incomeCats['Container Cargo']->id,
                    'amount' => rand(80000, 150000),
                    'date' => $formattedDate,
                    'description' => 'Container Logistics Cargo delivery',
                    'reference_source' => 'Client Invoice #' . (6000 + $i),
                    'created_by' => $superAdmin->id
                ]);
            }

            // Seed general expenses (office utility, store, etc.) on some days
            if ($i % 5 === 0) {
                Expense::create([
                    'category_id' => $expenseCats['Office Expenses']->id,
                    'amount' => rand(3000, 15000),
                    'date' => $formattedDate,
                    'description' => 'Monthly Office utility bills and office supplies',
                    'created_by' => $superAdmin->id
                ]);
            }

            // Seed Daily operational logs for each active vehicle
            foreach ($vehicles as $vehicle) {
                if ($vehicle->status !== 'active') continue;

                $driverId = $vehicle->assigned_driver_id;
                $mainKm = rand(120, 250);
                $localKm = rand(15, 60);
                $dieselLiters = rand(30, 70);
                $dieselAmount = $dieselLiters * 272.50; // standard price

                $pasgiGiven = 0;
                // Occasional Pasgi advance on daily logs
                if (rand(1, 10) === 5) {
                    $pasgiGiven = 5000;
                    
                    // Create Pasgi Advance record
                    PasgiAdvance::create([
                        'driver_id' => $driverId,
                        'vehicle_id' => $vehicle->id,
                        'amount' => $pasgiGiven,
                        'date' => $formattedDate,
                        'remarks' => 'Daily yard advance given for operational trip',
                        'created_by' => $superAdmin->id
                    ]);
                }

                FleetDailyData::create([
                    'date' => $formattedDate,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driverId,
                    'pasgi_given' => $pasgiGiven,
                    'daily_diesel_amount' => $dieselAmount,
                    'daily_diesel_liters' => $dieselLiters,
                    'main_km' => $mainKm,
                    'local_km' => $localKm,
                    'remarks' => 'Daily operational log entry',
                    'created_by' => $superAdmin->id
                ]);

                // Log the diesel cost under expenses to keep statistics correct
                Expense::create([
                    'category_id' => $expenseCats['Diesel Retail']->id,
                    'amount' => $dieselAmount,
                    'date' => $formattedDate,
                    'description' => 'Diesel refuel ' . $dieselLiters . ' Liters for vehicle ' . $vehicle->vehicle_number,
                    'vehicle_id' => $vehicle->id,
                    'created_by' => $superAdmin->id
                ]);
            }
        }

        // 9. Seed some Maintenance logs
        $maintenanceTypes = ['Mobile Oil', 'Filters', 'Tyres', 'Engine Repair'];
        $vendors = ['Super Auto Workshop', 'Hino City Service', 'Tyre Point LHR', 'D-Max Car Spa'];

        foreach ($vehicles as $vehicle) {
            // Seed 2 maintenance logs per vehicle in the last 30 days
            for ($m = 0; $m < 2; $m++) {
                Maintenance::create([
                    'vehicle_id' => $vehicle->id,
                    'maintenance_date' => Carbon::now()->subDays(rand(5, 25))->toDateString(),
                    'maintenance_type' => $maintenanceTypes[rand(0, 3)],
                    'amount' => rand(8000, 35000),
                    'vendor' => $vendors[rand(0, 3)],
                    'invoice_number' => 'INV-' . rand(10000, 99999),
                    'remarks' => 'Scheduled maintenance checks',
                    'created_by' => $superAdmin->id
                ]);
            }
        }

        // 10. Seed some Store purchases
        $items = ['Brake Pads', 'Air Filter', 'Engine Oil Drum', 'Wiper Blades', 'Tyre Tubes'];
        for ($s = 0; $s < 5; $s++) {
            StoreItem::create([
                'item_name' => $items[$s],
                'quantity' => rand(1, 5),
                'amount' => rand(5000, 20000),
                'date' => Carbon::now()->subDays(rand(5, 25))->toDateString(),
                'vehicle_id' => $vehicles[rand(0, 4)]->id,
                'vendor' => 'Auto Spare Parts Mart',
                'remarks' => 'Item purchased for reserve store stock',
                'created_by' => $superAdmin->id
            ]);
        }

        // 11. Seed Driver Salaries for the previous month (e.g. July 2026)
        $previousMonthStr = Carbon::now()->subMonth()->format('Y-m');
        $paymentDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        foreach ($drivers as $driver) {
            // Calculate total Pasgi advances in the previous month
            $advancesAmount = PasgiAdvance::where('driver_id', $driver->id)
                ->whereMonth('date', Carbon::now()->subMonth()->month)
                ->sum('amount');

            // Recover 50% of outstanding Pasgi advances
            $pasgiRecovery = $advancesAmount > 0 ? $advancesAmount * 0.5 : 0;
            $fine = rand(0, 2) === 1 ? rand(1000, 3000) : 0;
            $gross = $driver->base_salary;

            $salary = DriverSalary::create([
                'driver_id' => $driver->id,
                'salary_period' => $previousMonthStr,
                'gross_salary' => $gross,
                'fine' => $fine,
                'pasgi_adjustment' => $pasgiRecovery,
                'other_adjustment' => 0,
                'payment_date' => $paymentDate,
                'payment_status' => 'Paid',
                'remarks' => 'Salary disbursed for period: ' . $previousMonthStr,
                'created_by' => $superAdmin->id
            ]);

            // If Pasgi was recovered, create a Pasgi Adjustment record
            if ($pasgiRecovery > 0) {
                PasgiAdjustment::create([
                    'driver_id' => $driver->id,
                    'amount' => $pasgiRecovery,
                    'date' => $paymentDate,
                    'remarks' => 'Automatic recovery from salary for period: ' . $previousMonthStr,
                    'salary_id' => $salary->id,
                    'created_by' => $superAdmin->id
                ]);
            }

            // Mapped as general expense
            Expense::create([
                'category_id' => $expenseCats['Staff Salary']->id,
                'amount' => $salary->net_payable,
                'date' => $paymentDate,
                'description' => 'Driver salary payment to ' . $driver->name . ' for ' . $previousMonthStr,
                'created_by' => $superAdmin->id
            ]);
        }

        // 12. Seed Office Staff Employees
        $employeesData = [
            ['name' => 'Yaseen Ahmed', 'designation' => 'Yard Manager', 'contact' => '0300-1111111', 'base_salary' => 60000, 'status' => 'active'],
            ['name' => 'Aisha Bibi', 'designation' => 'Accountant', 'contact' => '0312-2222222', 'base_salary' => 55000, 'status' => 'active'],
            ['name' => 'Kamil Raza', 'designation' => 'Dispatcher', 'contact' => '0333-3333333', 'base_salary' => 45000, 'status' => 'active'],
        ];

        $employees = [];
        foreach ($employeesData as $empData) {
            $employees[] = Employee::firstOrCreate(['name' => $empData['name']], $empData);
        }

        // Seed some employee advances in the last month
        foreach ($employees as $employee) {
            if ($employee->name === 'Yaseen Ahmed' || $employee->name === 'Kamil Raza') {
                EmployeeAdvance::create([
                    'employee_id' => $employee->id,
                    'amount' => 15000,
                    'date' => Carbon::now()->subMonth()->startOfMonth()->addDays(5)->toDateString(),
                    'remarks' => 'Salary advance given for personal medical expenses',
                    'created_by' => $superAdmin->id
                ]);
            }
        }

        // Seed Employee Salaries for the previous month (July 2026)
        foreach ($employees as $employee) {
            $advancesAmount = EmployeeAdvance::where('employee_id', $employee->id)
                ->whereMonth('date', Carbon::now()->subMonth()->month)
                ->sum('amount');

            $advanceRecovery = $advancesAmount > 0 ? $advancesAmount * 0.333 : 0;
            $fine = ($employee->name === 'Kamil Raza') ? 1000 : 0;
            $gross = $employee->base_salary;

            $empSalary = EmployeeSalary::create([
                'employee_id' => $employee->id,
                'salary_period' => $previousMonthStr,
                'gross_salary' => $gross,
                'fine' => $fine,
                'advance_adjustment' => $advanceRecovery,
                'other_adjustment' => 0,
                'payment_date' => $paymentDate,
                'payment_status' => 'Paid',
                'remarks' => 'Monthly staff salary disbursed',
                'created_by' => $superAdmin->id
            ]);

            if ($advanceRecovery > 0) {
                EmployeeAdjustment::create([
                    'employee_id' => $employee->id,
                    'amount' => $advanceRecovery,
                    'date' => $paymentDate,
                    'remarks' => 'Partial recovery from salary for period: ' . $previousMonthStr,
                    'salary_id' => $empSalary->id,
                    'created_by' => $superAdmin->id
                ]);
            }

            Expense::create([
                'category_id' => $expenseCats['Staff Salary']->id,
                'amount' => $empSalary->net_payable,
                'date' => $paymentDate,
                'description' => 'Staff salary payment to ' . $employee->name . ' (' . $employee->designation . ') for ' . $previousMonthStr,
                'employee_id' => $employee->id,
                'created_by' => $superAdmin->id
            ]);
        }
    }
}
