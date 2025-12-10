<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * This migration handles tables across multiple databases:
     * - Shafiqah: animal, clinic, vet, vaccination
     * - Atiqah: section, slot
     * - Eilya: image (but references tables in other databases)
     */
    public function up(): void
    {
        /**
         * ATIQAH'S DATABASE - Inventory Module
         */
        // Create section table
        Schema::connection('atiqah')->create('section', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create slot table
        Schema::connection('atiqah')->create('slot', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('sectionID')->nullable();
            $table->timestamps();

            // Index for performance
            $table->index('sectionID');
        });

        // Add FK for slot -> section (same database, OK to use FK)
        Schema::connection('atiqah')->table('slot', function (Blueprint $table) {
            $table->foreign('sectionID')
                ->references('id')
                ->on('section')
                ->onDelete('set null');
        });

        /**
         * SHAFIQAH'S DATABASE - Animal & Medical Module
         */
        // Create clinic table
        Schema::connection('shafiqah')->create('clinic', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('contactNum', 30)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        // Create vet table
        Schema::connection('shafiqah')->create('vet', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contactNum', 30)->nullable();
            $table->string('specialization')->nullable();
            $table->string('license_no', 50)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->unsignedBigInteger('clinicID')->nullable();
            $table->timestamps();

            // Index for performance
            $table->index('clinicID');
        });

        // Add FK for vet -> clinic (same database, OK to use FK)
        Schema::connection('shafiqah')->table('vet', function (Blueprint $table) {
            $table->foreign('clinicID')
                ->references('id')
                ->on('clinic')
                ->onDelete('set null');
        });

        // Create animal table
        Schema::connection('shafiqah')->create('animal', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('species')->nullable();
            $table->text('health_details')->nullable();
            $table->string('age')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Unknown'])->default('Unknown');
            $table->string('adoption_status')->nullable();

            // Logical FK - references Eilya's rescue table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('rescueID')->nullable();

            // Logical FK - references Atiqah's slot table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('slotID')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index('rescueID');
            $table->index('slotID');
        });

        // Create vaccination table
        Schema::connection('shafiqah')->create('vaccination', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->date('next_due_date')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('costs', 10, 2)->nullable();
            $table->unsignedBigInteger('animalID')->nullable();
            $table->unsignedBigInteger('vetID')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('animalID');
            $table->index('vetID');
        });

        // Add FKs for vaccination (same database, OK to use FK)
        Schema::connection('shafiqah')->table('vaccination', function (Blueprint $table) {
            $table->foreign('animalID')
                ->references('id')
                ->on('animal')
                ->onDelete('set null');

            $table->foreign('vetID')
                ->references('id')
                ->on('vet')
                ->onDelete('set null');
        });

        /**
         * EILYA'S DATABASE - Image table
         * Images can belong to animals, reports, or clinics (all in different databases)
         */
        Schema::connection('eilya')->create('image', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');

            // Logical FK - references Shafiqah's animal table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('animalID')->nullable();

            // Logical FK - references Eilya's report table (same database, could use FK)
            $table->unsignedBigInteger('reportID')->nullable();

            // Logical FK - references Shafiqah's clinic table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('clinicID')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index('animalID');
            $table->index('reportID');
            $table->index('clinicID');
        });

        // Add FK for image -> report only (same database, OK to use FK)
        Schema::connection('eilya')->table('image', function (Blueprint $table) {
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
        /**
         * Drop FKs first
         */
        // Eilya's image table
        Schema::connection('eilya')->table('image', function (Blueprint $table) {
            $table->dropForeign(['reportID']);
        });

        // Shafiqah's vaccination table
        Schema::connection('shafiqah')->table('vaccination', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['vetID']);
        });

        // Shafiqah's vet table
        Schema::connection('shafiqah')->table('vet', function (Blueprint $table) {
            $table->dropForeign(['clinicID']);
        });

        // Atiqah's slot table
        Schema::connection('atiqah')->table('slot', function (Blueprint $table) {
            $table->dropForeign(['sectionID']);
        });

        /**
         * Then drop the tables
         */
        // Eilya's tables
        Schema::connection('eilya')->dropIfExists('image');

        // Shafiqah's tables
        Schema::connection('shafiqah')->dropIfExists('vaccination');
        Schema::connection('shafiqah')->dropIfExists('animal');
        Schema::connection('shafiqah')->dropIfExists('vet');
        Schema::connection('shafiqah')->dropIfExists('clinic');

        // Atiqah's tables
        Schema::connection('atiqah')->dropIfExists('slot');
        Schema::connection('atiqah')->dropIfExists('section');
    }
};
