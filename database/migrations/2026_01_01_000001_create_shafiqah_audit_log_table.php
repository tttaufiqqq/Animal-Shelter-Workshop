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
        Schema::connection('shafiqah')->create('audit_log', function (Blueprint $table) {
            $table->id();

            // User context
            $table->unsignedBigInteger('user_id')->nullable()->comment('User ID from taufiq database');
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role', 50)->nullable();

            // Action context
            $table->string('category', 50)->comment('animal, clinic, vet, medical, vaccination');
            $table->string('action', 100)->comment('created, updated, deleted, etc.');
            $table->string('entity_type', 50)->nullable()->comment('Animal, Clinic, Vet, etc.');
            $table->unsignedBigInteger('entity_id')->nullable();

            // Request context
            $table->timestamp('performed_at')->useCurrent();
            $table->string('ip_address', 45)->nullable()->comment('IPv4 or IPv6 address');
            $table->text('user_agent')->nullable();

            // Data context (JSON columns for flexible schema)
            $table->json('old_values')->nullable()->comment('Previous state before update/delete');
            $table->json('new_values')->nullable()->comment('New state after create/update');
            $table->json('metadata')->nullable()->comment('Additional contextual information');

            // Outcome
            $table->string('status', 20)->default('success')->comment('success, failure, error');
            $table->text('error_message')->nullable();

            // Indexes for performance
            $table->index('user_id', 'idx_audit_user_id');
            $table->index(['entity_type', 'entity_id'], 'idx_audit_entity');
            $table->index(['category', 'action'], 'idx_audit_category_action');
            $table->index('performed_at', 'idx_audit_performed_at');
        });

        // Add comment to table
        DB::connection('shafiqah')->statement(
            "ALTER TABLE audit_log COMMENT = 'Comprehensive audit log for all Shafiqah database operations'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('shafiqah')->dropIfExists('audit_log');
    }
};
