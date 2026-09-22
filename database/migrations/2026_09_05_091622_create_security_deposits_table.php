<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_deposits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnDelete();

            $table->decimal('amount', 10, 2);

            $table->decimal('deducted_amount', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2)->default(0);

            $table->enum('status', [
                'unpaid',
                'held',
                'partially_refunded',
                'fully_refunded',
                'forfeited'
            ])->default('unpaid');

            $table->date('refunded_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_deposits');
    }
};
