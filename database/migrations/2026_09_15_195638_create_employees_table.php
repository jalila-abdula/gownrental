<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('employee_code')->unique();
            $table->string('full_name');
            $table->string('contact_number')->nullable();
            $table->string('position')->default('Rental Staff');

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->date('hire_date')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
