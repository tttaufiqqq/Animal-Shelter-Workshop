<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * These tables belong to Danish's database (Booking & Adoption Module)
     */
    public function up(): void
    {
        Schema::connection('danish')->create('visit_list', function (Blueprint $table) {
            $table->id();

            // Logical FK - references Taufiq's users table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('userID')->unique(); // one visit list per user
            $table->timestamps();

            // Index for performance
            $table->index('userID');
        });

        Schema::connection('danish')->create('visit_list_animal', function (Blueprint $table) {
            $table->id();

            // FK to visit_list table (same database, OK to use FK)
            $table->unsignedBigInteger('listID');

            // Logical FK - references Shafiqah's animal table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('animalID');

            $table->text('remarks')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('listID');
            $table->index('animalID');

            // Prevent duplicate animals per visit list
            $table->unique(['listID', 'animalID']);
        });

        // Add FK for listID only (same database, OK to use FK)
        // Do NOT add FK for userID or animalID (cross-database)
        Schema::connection('danish')->table('visit_list_animal', function (Blueprint $table) {
            $table->foreign('listID')
                ->references('id')
                ->on('visit_list')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK first
        Schema::connection('danish')->table('visit_list_animal', function (Blueprint $table) {
            $table->dropForeign(['listID']);
        });

        // Drop dependent table FIRST
        Schema::connection('danish')->dropIfExists('visit_list_animal');
        // Then drop the parent table
        Schema::connection('danish')->dropIfExists('visit_list');
    }
};
