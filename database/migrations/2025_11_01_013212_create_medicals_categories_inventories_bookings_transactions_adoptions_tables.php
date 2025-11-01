<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /**
         * Step 1: Create base tables (no FKs yet)
         */
        Schema::create('medicals', function (Blueprint $table) {
            $table->id();
            $table->date('date_checkup');
            $table->string('treatment_type')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('action')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('costs', 10, 2)->nullable();
            $table->unsignedBigInteger('vetID')->nullable();    // FK later
            $table->unsignedBigInteger('animalID')->nullable(); // FK later
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('main')->nullable();
            $table->string('sub')->nullable();
            $table->timestamps();
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->integer('quantity')->default(0);
            $table->string('brand')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('slotID')->nullable();     // FK later
            $table->unsignedBigInteger('categoryID')->nullable(); // FK later
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->date('appointment_date')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('animalID')->nullable(); // FK later
            $table->unsignedBigInteger('userID')->nullable();   // FK later
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('date')->useCurrent();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('userID')->nullable(); // FK later
            $table->timestamps();
        });

        Schema::create('adoptions', function (Blueprint $table) {
            $table->id();
            $table->decimal('fee', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('bookingID')->nullable();     // FK later
            $table->unsignedBigInteger('transactionID')->nullable(); // FK later
            $table->timestamps();
        });

        Schema::create('animal_booking', function (Blueprint $table) {
            $table->unsignedBigInteger('animalID');  // FK later
            $table->unsignedBigInteger('bookingID'); // FK later
            $table->primary(['animalID', 'bookingID']);
        });

        /**
         * Step 2: Add foreign key constraints
         */
        Schema::table('medicals', function (Blueprint $table) {
            $table->foreign('vetID')
                  ->references('id')
                  ->on('vets')
                  ->onDelete('set null');

            $table->foreign('animalID')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('cascade');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->foreign('slotID')
                  ->references('id')
                  ->on('slots')
                  ->onDelete('set null');

            $table->foreign('categoryID')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('animalID')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('set null');

            $table->foreign('userID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('userID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        Schema::table('adoptions', function (Blueprint $table) {
            $table->foreign('bookingID')
                  ->references('id')
                  ->on('bookings')
                  ->onDelete('cascade');

            $table->foreign('transactionID')
                  ->references('id')
                  ->on('transactions')
                  ->onDelete('set null');
        });

        Schema::table('animal_booking', function (Blueprint $table) {
            $table->foreign('animalID')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('cascade');

            $table->foreign('bookingID')
                  ->references('id')
                  ->on('bookings')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        /**
         * Drop FKs first in reverse order
         */
        Schema::table('animal_booking', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['bookingID']);
        });

        Schema::table('adoptions', function (Blueprint $table) {
            $table->dropForeign(['bookingID']);
            $table->dropForeign(['transactionID']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['userID']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['userID']);
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['slotID']);
            $table->dropForeign(['categoryID']);
        });

        Schema::table('medicals', function (Blueprint $table) {
            $table->dropForeign(['vetID']);
            $table->dropForeign(['animalID']);
        });

        /**
         * Drop tables
         */
        Schema::dropIfExists('animal_booking');
        Schema::dropIfExists('adoptions');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('medicals');
    }
};
