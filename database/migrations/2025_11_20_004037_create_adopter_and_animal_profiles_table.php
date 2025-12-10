<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration handles tables across multiple databases:
     * - Taufiq: adopter_profile
     * - Shafiqah: animal_profile
     */
    public function up(): void
    {
        /**
         * TAUFIQ'S DATABASE - User Management Module
         */
        Schema::connection('taufiq')->create('adopter_profile', function (Blueprint $table) {
            $table->id();
            // Adopter matching attributes
            $table->string('housing_type')->nullable(); // condo, landed
            $table->boolean('has_children')->default(false);
            $table->boolean('has_other_pets')->default(false);
            $table->string('activity_level')->nullable(); // low/medium/high
            $table->string('experience')->nullable(); // beginner/intermediate/expert
            $table->string('preferred_species')->nullable(); // cat/dog
            $table->string('preferred_size')->nullable(); // small/medium/large

            // FK to users table (same database, OK to use FK)
            $table->unsignedBigInteger('adopterID');
            $table->timestamps();

            // Index for performance
            $table->index('adopterID');
        });

        // Add FK for adopter_profile -> users (same database, OK to use FK)
        Schema::connection('taufiq')->table('adopter_profile', function (Blueprint $table) {
            $table->foreign('adopterID')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        /**
         * SHAFIQAH'S DATABASE - Animal & Medical Module
         */
        Schema::connection('shafiqah')->create('animal_profile', function (Blueprint $table) {
            $table->id();
            // Animal matching attributes
            $table->string('age')->nullable(); // kitten/puppy/adult/senior
            $table->string('size')->nullable(); // small/medium/large
            $table->string('energy_level')->nullable(); // low/medium/high
            $table->boolean('good_with_kids')->default(false);
            $table->boolean('good_with_pets')->default(false);
            $table->string('temperament')->nullable(); // calm/active/shy
            $table->string('medical_needs')->nullable();

            // FK to animal table (same database, OK to use FK)
            $table->unsignedBigInteger('animalID');
            $table->timestamps();

            // Index for performance
            $table->index('animalID');
        });

        // Add FK for animal_profile -> animal (same database, OK to use FK)
        Schema::connection('shafiqah')->table('animal_profile', function (Blueprint $table) {
            $table->foreign('animalID')
                ->references('id')
                ->on('animal')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK first
        Schema::connection('shafiqah')->table('animal_profile', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
        });

        Schema::connection('taufiq')->table('adopter_profile', function (Blueprint $table) {
            $table->dropForeign(['adopterID']);
        });

        // Drop tables
        Schema::connection('shafiqah')->dropIfExists('animal_profile');
        Schema::connection('taufiq')->dropIfExists('adopter_profile');
    }
};
