<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->text('measurements')->nullable();
            $table->string('government_id_photo_path')->nullable();
            $table->string('physical_id_photo_path')->nullable();
            $table->string('id_safe_slot')->nullable();
            $table->timestamp('agreement_accepted_at')->nullable();
            $table->string('collateral_status')->default('not_received');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','gcash','card') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE payments SET payment_method = 'cash' WHERE payment_method = 'card'");
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','gcash') NOT NULL");
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['measurements', 'government_id_photo_path', 'physical_id_photo_path', 'id_safe_slot', 'agreement_accepted_at', 'collateral_status']);
        });
    }
};
