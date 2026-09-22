<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnDelete();

            $table->foreignId('gown_id')
                ->constrained('gowns')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('rental_price', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);

            $table->integer('quantity')->default(1);

            $table->timestamps();

            $table->unique(['reservation_id', 'gown_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};
