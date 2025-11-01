<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /**
         * Step 1: Create tables first (no foreign keys yet)
         */
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('section')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('contactNum', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('vets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contactNum', 30)->nullable();
            $table->string('specialization')->nullable();
            $table->string('license_no', 50)->nullable();
            $table->unsignedBigInteger('clinicID')->nullable(); // FK later
            $table->timestamps();
        });

        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->date('date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('animalID')->nullable(); // FK later
            $table->unsignedBigInteger('vetID')->nullable();    // FK later
            $table->timestamps();
        });

        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->string('species')->nullable();
            $table->text('health_details')->nullable();
            $table->integer('age')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Unknown'])->default('Unknown');
            $table->string('adoption_status')->nullable();
            $table->date('arrival_date')->nullable();
            $table->string('medical_status')->nullable();
            $table->unsignedBigInteger('rescueID')->nullable();     // FK later
            $table->unsignedBigInteger('slotID')->nullable();       // FK later
            $table->unsignedBigInteger('vaccinationID')->nullable(); // FK later
            $table->timestamps();
        });

        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->unsignedBigInteger('animalID')->nullable(); // FK later
            $table->unsignedBigInteger('reportID')->nullable(); // FK later
            $table->timestamps();
        });

        /**
         * Step 2: Add foreign key constraints
         */
        Schema::table('vets', function (Blueprint $table) {
            $table->foreign('clinicID')
                  ->references('id')
                  ->on('clinics')
                  ->onDelete('set null');
        });

        Schema::table('vaccinations', function (Blueprint $table) {
            $table->foreign('animalID')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('set null');

            $table->foreign('vetID')
                  ->references('id')
                  ->on('vets')
                  ->onDelete('set null');
        });

        Schema::table('animals', function (Blueprint $table) {
            $table->foreign('rescueID')
                  ->references('id')
                  ->on('rescues')
                  ->onDelete('set null');

            $table->foreign('slotID')
                  ->references('id')
                  ->on('slots')
                  ->onDelete('set null');

            $table->foreign('vaccinationID')
                  ->references('id')
                  ->on('vaccinations')
                  ->onDelete('set null');
        });

        Schema::table('images', function (Blueprint $table) {
            $table->foreign('animalID')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('cascade');

            $table->foreign('reportID')
                  ->references('id')
                  ->on('reports')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        /**
         * Drop FKs first (in reverse order)
         */
        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['reportID']);
        });

        Schema::table('animals', function (Blueprint $table) {
            $table->dropForeign(['rescueID']);
            $table->dropForeign(['slotID']);
            $table->dropForeign(['vaccinationID']);
        });

        Schema::table('vaccinations', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['vetID']);
        });

        Schema::table('vets', function (Blueprint $table) {
            $table->dropForeign(['clinicID']);
        });

        /**
         * Then drop the tables
         */
        Schema::dropIfExists('images');
        Schema::dropIfExists('animals');
        Schema::dropIfExists('vaccinations');
        Schema::dropIfExists('vets');
        Schema::dropIfExists('clinics');
        Schema::dropIfExists('slots');
    }
};
