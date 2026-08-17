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
        Schema::create('driver_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->string('salary_period'); // YYYY-MM
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('fine', 12, 2)->default(0);
            $table->decimal('pasgi_adjustment', 12, 2)->default(0);
            $table->decimal('other_adjustment', 12, 2)->default(0);
            $table->decimal('net_payable', 12, 2);
            $table->date('payment_date')->nullable();
            $table->string('payment_status')->default('Pending'); // Paid, Pending
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
        Schema::dropIfExists('driver_salaries');
    }
};
