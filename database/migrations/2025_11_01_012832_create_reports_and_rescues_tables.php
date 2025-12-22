<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Report and Rescue tables belong to Eilya's database (Reporting & Rescue Module)
     */
    public function up(): void
    {
        // Create report table in Eilya's database
        Schema::connection('eilya')->create('report', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('report_status', 50)->nullable();
            $table->text('description')->nullable();

            // Logical foreign key - references Taufiq's users table (cross-database)
            $table->unsignedBigInteger('userID')->nullable();
            $table->timestamps();

            // Add index for performance (no foreign key constraint for cross-database)
            $table->index('userID');
        });

        // Create rescue table in Eilya's database
        Schema::connection('eilya')->create('rescue', function (Blueprint $table) {
            $table->id();
            $table->string('status', 50)->nullable();
            $table->string('priority', 20)->default('normal'); // critical, high, normal
            $table->text('remarks')->nullable();

            // Logical foreign key - references report table (same database - can use FK)
            $table->unsignedBigInteger('reportID')->nullable();

            // Logical foreign key - references Taufiq's users table (cross-database)
            $table->unsignedBigInteger('caretakerID')->nullable();
            $table->timestamps();

            // Add indexes for performance
            $table->index('reportID');
            $table->index('caretakerID');
            $table->index('priority'); // Index for priority-based queries
        });

        // Add foreign key constraint for reportID only (same database)
        // Do NOT add foreign keys for userID or caretakerID (cross-database to Taufiq)
        Schema::connection('eilya')->table('rescue', function (Blueprint $table) {
            $table->foreign('reportID')
                ->references('id')
                ->on('report')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key first
        Schema::connection('eilya')->table('rescue', function (Blueprint $table) {
            $table->dropForeign(['reportID']);
        });

        // Then drop the tables
        Schema::connection('eilya')->dropIfExists('rescue');
        Schema::connection('eilya')->dropIfExists('report');
    }
};
