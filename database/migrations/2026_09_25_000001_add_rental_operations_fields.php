<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gown_releases', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('release_date')->nullable();
            $table->time('release_time')->nullable();
            $table->string('condition_before', 30)->nullable();
            $table->text('notes')->nullable();
        });

        Schema::table('gown_returns', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('actual_return_date')->nullable();
            $table->time('actual_return_time')->nullable();
            $table->string('condition_after', 30)->nullable();
            $table->unsignedInteger('late_days')->default(0);
            $table->text('notes')->nullable();
        });

        Schema::table('cleaning_records', function (Blueprint $table) {
            $table->foreignId('gown_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('cleaning_date')->nullable();
            $table->string('cleaning_type', 100)->nullable();
            $table->string('status', 30)->default('pending');
            $table->decimal('cost', 10, 2)->default(0);
            $table->text('notes')->nullable();
        });

        Schema::table('damage_reports', function (Blueprint $table) {
            $table->foreignId('gown_return_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('gown_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('damage_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('repair_cost', 10, 2)->default(0);
            $table->string('severity', 30)->default('minor');
            $table->text('photos')->nullable();
        });

        Schema::table('penalties', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('gown_return_id')->nullable()->constrained()->nullOnDelete();
            $table->string('penalty_type', 100)->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waived_at')->nullable();
            $table->text('waiver_reason')->nullable();
        });

        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->foreignId('gown_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('maintenance_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->date('maintenance_date')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('setting_key')->nullable()->unique();
            $table->text('setting_value')->nullable();
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gown_releases', function (Blueprint $table) {
            $table->dropUnique(['reservation_id']);
            $table->dropConstrainedForeignId('reservation_id');
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn(['release_date', 'release_time', 'condition_before', 'notes']);
        });
        Schema::table('gown_returns', function (Blueprint $table) {
            $table->dropUnique(['reservation_id']);
            $table->dropConstrainedForeignId('reservation_id');
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn(['actual_return_date', 'actual_return_time', 'condition_after', 'late_days', 'notes']);
        });
        Schema::table('cleaning_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gown_id');
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn(['cleaning_date', 'cleaning_type', 'status', 'cost', 'notes']);
        });
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gown_return_id');
            $table->dropConstrainedForeignId('gown_id');
            $table->dropColumn(['damage_type', 'description', 'repair_cost', 'severity', 'photos']);
        });
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservation_id');
            $table->dropConstrainedForeignId('gown_return_id');
            $table->dropConstrainedForeignId('waived_by');
            $table->dropColumn(['penalty_type', 'amount', 'status', 'waived_at', 'waiver_reason']);
        });
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gown_id');
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn(['maintenance_type', 'description', 'cost', 'maintenance_date', 'status', 'notes']);
        });
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropUnique(['setting_key']);
            $table->dropColumn(['setting_key', 'setting_value', 'description']);
        });
    }
};
