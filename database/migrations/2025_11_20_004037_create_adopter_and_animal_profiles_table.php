<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adopter_profile', function (Blueprint $table) {
            $table->id();
            // Adopter matching attributes
            $table->string('housing_type')->nullable(); // condo, landed
            $table->boolean('has_children')->default(false);
            $table->boolean('has_other_pets')->default(false);
            $table->string('activity_level')->nullable(); // low/medium/high
            $table->string('experience')->nullable(); // beginner/intermediate/expert
            $table->string('preferred_species')->nullable(); // cat/dog
            $table->string('preferred_size')->nullable(); // small/medium/large
            // FK to USERS table
            $table->unsignedBigInteger('adopterID');
            $table->foreign('adopterID')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('animal_profile', function (Blueprint $table) {
            $table->id();
            // Animal matching attributes
            $table->string('age')->nullable(); // kitten/puppy/adult/senior
            $table->string('size')->nullable(); // small/medium/large
            $table->string('energy_level')->nullable(); // low/medium/high
            $table->boolean('good_with_kids')->default(false);
            $table->boolean('good_with_pets')->default(false);
            $table->string('temperament')->nullable(); // calm/active/shy
            $table->string('medical_needs')->nullable();
            // FK to ANIMALS table
            $table->unsignedBigInteger('animalID');
            $table->foreign('animalID')
                  ->references('id')->on('animal')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adopter_profile');
        Schema::dropIfExists('animal_profile');
    }
};
