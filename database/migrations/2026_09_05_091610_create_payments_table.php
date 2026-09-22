<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('payment_reference')->unique();

            $table->enum('payment_type', [
                'downpayment',
                'rental_balance',
                'security_deposit',
                'penalty',
                'damage_fee',
                'other'
            ]);

            $table->enum('payment_method', [
                'cash',
                'gcash'
            ]);

            $table->decimal('amount', 10, 2);

            $table->string('proof_of_payment')->nullable();

            $table->enum('status', [
                'pending',
                'verified',
                'rejected',
                'refunded'
            ])->default('pending');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['reservation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
