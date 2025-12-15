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
        Schema::connection('backup')->create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('connection_name'); // eilya, atiqah, shafiqah, danish, taufiq
            $table->string('backup_type')->default('manual'); // manual, scheduled
            $table->string('status'); // success, failed
            $table->string('file_path')->nullable(); // Path to backup file
            $table->bigInteger('file_size')->nullable(); // Size in bytes (bigInteger for large files)
            $table->text('error_message')->nullable(); // Error details if failed
            $table->timestamp('backup_started_at')->nullable();
            $table->timestamp('backup_completed_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // Backup duration
            $table->unsignedBigInteger('admin_id')->nullable(); // Who triggered manual backup
            $table->timestamps();

            // Indexes for quick lookups
            $table->index('connection_name');
            $table->index('status');
            $table->index('created_at');
            $table->index('backup_type');

            // PostgreSQL-specific: Composite index for common queries
            $table->index(['connection_name', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('backup')->dropIfExists('backup_logs');
    }
};
