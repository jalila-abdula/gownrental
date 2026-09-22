<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->string('reservation_code')->unique();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('pickup_date');
            $table->date('return_date');

            $table->decimal('rental_total', 10, 2)->default(0);
            $table->decimal('security_deposit_total', 10, 2)->default(0);

            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);

            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);

            $table->enum('status', [
                'pending',
                'awaiting_payment',
                'confirmed',
                'ready_for_pickup',
                'released',
                'returned',
                'completed',
                'cancelled',
                'rejected',
                'overdue'
            ])->default('pending');

            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['pickup_date', 'return_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
