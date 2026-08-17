<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fleet_daily_data', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->decimal('pasgi_given', 12, 2)->default(0);
            $table->decimal('daily_diesel_amount', 12, 2)->default(0);
            $table->decimal('daily_diesel_liters', 8, 2)->default(0);
            $table->integer('main_km')->default(0);
            $table->integer('local_km')->default(0);
            $table->integer('total_km')->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_daily_data');
    }
};
