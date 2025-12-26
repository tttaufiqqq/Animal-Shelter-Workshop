<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add performance indexes to audit_logs table
     */
    public function up(): void
    {
        Schema::connection('taufiq')->table('audit_logs', function (Blueprint $table) {
            // Individual indexes for specific queries
            $table->index('user_email', 'idx_audit_user_email');
            $table->index('category', 'idx_audit_category');
            $table->index('action', 'idx_audit_action');
            $table->index('performed_at', 'idx_audit_performed_at');
            $table->index('ip_address', 'idx_audit_ip_address');

            // Composite index for most common query pattern (category + user_email + performed_at)
            // This dramatically speeds up user activity queries
            $table->index(['category', 'user_email', 'performed_at'], 'idx_audit_category_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('taufiq')->table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_user_email');
            $table->dropIndex('idx_audit_category');
            $table->dropIndex('idx_audit_action');
            $table->dropIndex('idx_audit_performed_at');
            $table->dropIndex('idx_audit_ip_address');
            $table->dropIndex('idx_audit_category_user_date');
        });
    }
};
