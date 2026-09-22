<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gowns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('gown_code')->unique();
            $table->string('name');

            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('style')->nullable();

            $table->decimal('rental_price', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);

            $table->decimal('purchase_price', 10, 2)->nullable();

            $table->text('description')->nullable();
            $table->text('measurements')->nullable();

            $table->string('image')->nullable();

            $table->enum('condition', [
                'excellent',
                'good',
                'fair',
                'damaged'
            ])->default('good');

            $table->enum('status', [
                'available',
                'reserved',
                'rented',
                'for_cleaning',
                'under_maintenance',
                'damaged',
                'unavailable',
                'retired'
            ])->default('available');

            $table->date('date_purchased')->nullable();

            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['size', 'color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gowns');
    }
};
