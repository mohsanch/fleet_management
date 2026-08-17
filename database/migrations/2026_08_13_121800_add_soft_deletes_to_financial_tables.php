<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'incomes',
            'expenses',
            'maintenances',
            'store_items',
            'fleet_daily_data',
            'driver_salaries',
            'employee_salaries',
            'pasgi_advances',
            'employee_advances',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'incomes',
            'expenses',
            'maintenances',
            'store_items',
            'fleet_daily_data',
            'driver_salaries',
            'employee_salaries',
            'pasgi_advances',
            'employee_advances',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
