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
        Schema::create('slot', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('section')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('clinic', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('contactNum', 30)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        Schema::create('vet', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contactNum', 30)->nullable();
            $table->string('specialization')->nullable();
            $table->string('license_no', 50)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->unsignedBigInteger('clinicID')->nullable(); // FK later
            $table->timestamps();
        });

        Schema::create('vaccination', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->date('next_due_date')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('costs', 10, 2)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('animalID')->nullable(); // FK later
            $table->unsignedBigInteger('vetID')->nullable();    // FK later
        });

        Schema::create('animal', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('species')->nullable();
            $table->text('health_details')->nullable();
            $table->string('age')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Unknown'])->default('Unknown');
            $table->string('adoption_status')->nullable();
            $table->unsignedBigInteger('rescueID')->nullable();     // FK later
            $table->unsignedBigInteger('slotID')->nullable();       // FK later
            $table->timestamps();
        });

        Schema::create('image', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->unsignedBigInteger('animalID')->nullable(); // FK later
            $table->unsignedBigInteger('reportID')->nullable(); // FK later
            $table->unsignedBigInteger('clinicID')->nullable(); // FK later
            $table->timestamps();
        });

        /**
         * Step 2: Add foreign key constraints
         */
        Schema::table('vet', function (Blueprint $table) {
            $table->foreign('clinicID')
                  ->references('id')
                  ->on('clinic')
                  ->onDelete('set null');
        });

        Schema::table('vaccination', function (Blueprint $table) {
            $table->foreign('animalID')
                  ->references('id')
                  ->on('animal')
                  ->onDelete('set null');

            $table->foreign('vetID')
                  ->references('id')
                  ->on('vet')
                  ->onDelete('set null');
        });

        Schema::table('animal', function (Blueprint $table) {
            $table->foreign('rescueID')
                  ->references('id')
                  ->on('rescue')
                  ->onDelete('set null');

            $table->foreign('slotID')
                  ->references('id')
                  ->on('slot')
                  ->onDelete('set null');
        });

        Schema::table('image', function (Blueprint $table) {
            $table->foreign('animalID')
                  ->references('id')
                  ->on('animal')
                  ->onDelete('cascade');

            $table->foreign('reportID')
                  ->references('id')
                  ->on('report')
                  ->onDelete('cascade');
            $table->foreign('clinicID')
                  ->references('id')
                  ->on('clinic')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        /**
         * Drop FKs first (in reverse order)
         */
        Schema::table('image', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['reportID']);
            $table->dropForeign(['clinicID']);
        });

        Schema::table('animal', function (Blueprint $table) {
            $table->dropForeign(['rescueID']);
            $table->dropForeign(['slotID']);
        });

        Schema::table('vaccination', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['vetID']);
        });

        Schema::table('vet', function (Blueprint $table) {
            $table->dropForeign(['clinicID']);
        });

        /**
         * Then drop the tables
         */
        Schema::dropIfExists('image');
        Schema::dropIfExists('animal');
        Schema::dropIfExists('vaccination');
        Schema::dropIfExists('vet');
        Schema::dropIfExists('clinic');
        Schema::dropIfExists('slot');
    }
};
