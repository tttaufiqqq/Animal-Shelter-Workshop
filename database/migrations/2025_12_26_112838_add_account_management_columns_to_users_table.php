<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('taufiq')->table('users', function (Blueprint $table) {
            // Account status: active, suspended, locked
            $table->string('account_status', 20)->default('active')->after('password');

            // Suspension tracking
            $table->timestamp('suspended_at')->nullable()->after('account_status');
            $table->unsignedBigInteger('suspended_by')->nullable()->after('suspended_at');
            $table->text('suspension_reason')->nullable()->after('suspended_by');

            // Temporary lock (for security issues)
            $table->timestamp('locked_until')->nullable()->after('suspension_reason');
            $table->text('lock_reason')->nullable()->after('locked_until');

            // Failed login tracking
            $table->integer('failed_login_attempts')->default(0)->after('lock_reason');
            $table->timestamp('last_failed_login_at')->nullable()->after('failed_login_attempts');

            // Password reset requirement
            $table->boolean('require_password_reset')->default(false)->after('last_failed_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('taufiq')->table('users', function (Blueprint $table) {
            $table->dropColumn([
                'account_status',
                'suspended_at',
                'suspended_by',
                'suspension_reason',
                'locked_until',
                'lock_reason',
                'failed_login_attempts',
                'last_failed_login_at',
                'require_password_reset',
            ]);
        });
    }
};
