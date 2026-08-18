<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create branches table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default branches
        DB::table('branches')->insert([
            ['name' => 'Sahiwal', 'code' => 'SWL', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sheikhupura', 'code' => 'SKP', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $defaultBranchId = DB::table('branches')->where('code', 'SWL')->value('id') ?? 1;

        // 2. Add branch_id to existing tables
        $tables = [
            'users',
            'drivers',
            'employees',
            'vehicles',
            'fleet_daily_data',
            'maintenances',
            'store_items',
            'incomes',
            'expenses',
            'driver_salaries',
            'employee_salaries',
            'pasgi_advances',
            'employee_advances',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                    $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                });

                // Assign all existing records in this table to Sahiwal (SWL) by default
                DB::table($tableName)->update(['branch_id' => $defaultBranchId]);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'drivers',
            'employees',
            'vehicles',
            'fleet_daily_data',
            'maintenances',
            'store_items',
            'incomes',
            'expenses',
            'driver_salaries',
            'employee_salaries',
            'pasgi_advances',
            'employee_advances',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                });
            }
        }

        Schema::dropIfExists('branches');
    }
};
