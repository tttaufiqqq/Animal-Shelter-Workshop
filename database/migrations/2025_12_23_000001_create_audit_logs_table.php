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
        Schema::connection('taufiq')->create('audit_logs', function (Blueprint $table) {
            $table->id();

            // WHO performed the action
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role', 50)->nullable();

            // WHAT action was performed
            $table->string('category', 50)->index(); // 'authentication', 'payment', 'animal', 'rescue'
            $table->string('action', 100)->index(); // 'login_success', 'payment_completed', etc.
            $table->string('entity_type', 50)->nullable()->index(); // 'Booking', 'Animal', 'Rescue', etc.
            $table->unsignedBigInteger('entity_id')->nullable()->index(); // ID of the affected record

            // WHERE in the distributed system
            $table->string('source_database', 20)->nullable(); // 'taufiq', 'eilya', 'shafiqah', 'atiqah', 'danish'

            // WHEN it happened
            $table->timestamp('performed_at')->index();

            // HOW/WHY (context)
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->text('user_agent')->nullable(); // Browser/device info
            $table->text('request_url')->nullable(); // Full URL
            $table->string('http_method', 10)->nullable(); // GET, POST, PUT, DELETE

            // DETAILS (JSONB for PostgreSQL flexibility)
            $table->jsonb('old_values')->nullable(); // Before state
            $table->jsonb('new_values')->nullable(); // After state
            $table->jsonb('metadata')->nullable(); // Additional context (correlation_id, etc.)

            // OUTCOME
            $table->string('status', 20)->index(); // 'success', 'failure', 'error'
            $table->text('error_message')->nullable(); // If status = 'failure' or 'error'

            $table->timestamps();

            // Foreign key to users table (same database - taufiq)
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Composite indexes for common query patterns
            $table->index(['user_id', 'performed_at']);
            $table->index(['category', 'action', 'performed_at']);
            $table->index(['status', 'performed_at']);
        });

        // GIN indexes for JSONB querying (PostgreSQL specific)
        DB::connection('taufiq')->statement('CREATE INDEX idx_audit_metadata ON audit_logs USING GIN (metadata)');
        DB::connection('taufiq')->statement('CREATE INDEX idx_audit_old_values ON audit_logs USING GIN (old_values)');
        DB::connection('taufiq')->statement('CREATE INDEX idx_audit_new_values ON audit_logs USING GIN (new_values)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('taufiq')->dropIfExists('audit_logs');
    }
};
