<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('customer_code')->unique();

            $table->string('full_name');
            $table->string('contact_number');
            $table->string('email')->nullable();

            $table->text('address')->nullable();

            $table->enum('status', [
                'active',
                'restricted',
                'blocked'
            ])->default('active');

            $table->timestamps();

            $table->index('contact_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
