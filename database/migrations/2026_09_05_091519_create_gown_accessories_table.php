<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gown_accessories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gown_id')
                ->constrained('gowns')
                ->cascadeOnDelete();

            $table->foreignId('accessory_id')
                ->constrained('accessories')
                ->cascadeOnDelete();

            $table->integer('quantity')->default(1);

            $table->timestamps();

            $table->unique(['gown_id', 'accessory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gown_accessories');
    }
};
