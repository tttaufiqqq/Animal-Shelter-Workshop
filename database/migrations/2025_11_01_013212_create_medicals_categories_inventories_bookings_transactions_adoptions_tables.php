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
        Schema::create('medical', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_type')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('action')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('costs', 10, 2)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('vetID')->nullable();    // FK later
            $table->unsignedBigInteger('animalID')->nullable(); // FK later
        });

        Schema::create('category', function (Blueprint $table) {
            $table->id();
            $table->string('main')->nullable();
            $table->string('sub')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory', function (Blueprint $table) {
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

        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->date('appointment_date')->nullable();
            $table->time('appointment_time');
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('animalID')->nullable(); // FK later
            $table->unsignedBigInteger('userID')->nullable();   // FK later
            $table->timestamps();
        });

        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->string('type')->nullable();
            $table->string('bill_code')->nullable();
            $table->string('reference_no')->nullable();
            $table->unsignedBigInteger('userID')->nullable(); // FK later
            $table->timestamps();
        });

        Schema::create('adoption', function (Blueprint $table) {
            $table->id();
            $table->decimal('fee', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('bookingID')->nullable();     // FK later
            $table->unsignedBigInteger('transactionID')->nullable(); // FK later
            $table->timestamps();
        });
        /**
         * Step 2: Add foreign key constraints
         */
        Schema::table('medical', function (Blueprint $table) {
            $table->foreign('vetID')
                  ->references('id')
                  ->on('vet')
                  ->onDelete('set null');

            $table->foreign('animalID')
                  ->references('id')
                  ->on('animal')
                  ->onDelete('cascade');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->foreign('slotID')
                  ->references('id')
                  ->on('slot')
                  ->onDelete('set null');

            $table->foreign('categoryID')
                  ->references('id')
                  ->on('category')
                  ->onDelete('set null');
        });

        Schema::table('booking', function (Blueprint $table) {
            $table->foreign('animalID')
                  ->references('id')
                  ->on('animal')
                  ->onDelete('set null');

            $table->foreign('userID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        Schema::table('transaction', function (Blueprint $table) {
            $table->foreign('userID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        Schema::table('adoption', function (Blueprint $table) {
            $table->foreign('bookingID')
                  ->references('id')
                  ->on('booking')
                  ->onDelete('cascade');

            $table->foreign('transactionID')
                  ->references('id')
                  ->on('transaction')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('adoption', function (Blueprint $table) {
            $table->dropForeign(['bookingID']);
            $table->dropForeign(['transactionID']);
        });

        Schema::table('transaction', function (Blueprint $table) {
            $table->dropForeign(['userID']);
        });

        Schema::table('booking', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['userID']);
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign(['slotID']);
            $table->dropForeign(['categoryID']);
        });

        Schema::table('medical', function (Blueprint $table) {
            $table->dropForeign(['vetID']);
            $table->dropForeign(['animalID']);
        });

        /**
         * Drop tables
         */
        Schema::dropIfExists('adoption');
        Schema::dropIfExists('transaction');
        Schema::dropIfExists('booking');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('category');
        Schema::dropIfExists('medical');
    }
};
