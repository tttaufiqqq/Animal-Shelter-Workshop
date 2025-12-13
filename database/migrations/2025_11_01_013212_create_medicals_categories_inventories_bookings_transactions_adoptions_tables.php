<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * This migration handles tables across multiple databases:
     * - Shafiqah: medical
     * - Atiqah: category, inventory
     * - Danish: booking, transaction, adoption
     */
    public function up(): void
    {
        /**
         * SHAFIQAH'S DATABASE - Animal & Medical Module
         */
        Schema::connection('shafiqah')->create('medical', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_type', 100)->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('action')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('costs', 10, 2)->nullable();
            $table->unsignedBigInteger('vetID')->nullable();
            $table->unsignedBigInteger('animalID')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('vetID');
            $table->index('animalID');
        });

        // Add FKs for medical (same database, OK to use FK)
        Schema::connection('shafiqah')->table('medical', function (Blueprint $table) {
            $table->foreign('vetID')
                ->references('id')
                ->on('vet')
                ->onDelete('set null');

            $table->foreign('animalID')
                ->references('id')
                ->on('animal')
                ->onDelete('cascade');
        });

        /**
         * ATIQAH'S DATABASE - Inventory Module
         */
        Schema::connection('atiqah')->create('category', function (Blueprint $table) {
            $table->id();
            $table->string('main', 255)->nullable();
            $table->string('sub', 255)->nullable();
            $table->timestamps();
        });

        Schema::connection('atiqah')->create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_name', 255);
            $table->integer('quantity')->default(0);
            $table->string('brand', 255)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('status', 50)->nullable();
            $table->unsignedBigInteger('slotID')->nullable();
            $table->unsignedBigInteger('categoryID')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('slotID');
            $table->index('categoryID');
        });

        // Add FKs for inventory (same database, OK to use FK)
        Schema::connection('atiqah')->table('inventory', function (Blueprint $table) {
            $table->foreign('slotID')
                ->references('id')
                ->on('slot')
                ->onDelete('set null');

            $table->foreign('categoryID')
                ->references('id')
                ->on('category')
                ->onDelete('set null');
        });

        /**
         * DANISH'S DATABASE - Booking & Adoption Module
         */
        Schema::connection('danish')->create('booking', function (Blueprint $table) {
            $table->id();
            $table->date('appointment_date')->nullable();
            $table->time('appointment_time');
            $table->string('status', 50)->nullable();
            $table->text('remarks')->nullable();

            // Logical FK - references Taufiq's users table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('userID')->nullable();
            $table->timestamps();

            // Index for performance
            $table->index('userID');
        });

        Schema::connection('danish')->create('transaction', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('bill_code', 100)->nullable();
            $table->string('reference_no', 100)->nullable();

            // Logical FK - references Taufiq's users table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('userID')->nullable();
            $table->timestamps();

            // Index for performance
            $table->index('userID');
        });

        Schema::connection('danish')->create('adoption', function (Blueprint $table) {
            $table->id();
            $table->decimal('fee', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('bookingID')->nullable();
            $table->unsignedBigInteger('transactionID')->nullable();

            // Logical FK - references Shafiqah's animal table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('animalID')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('bookingID');
            $table->index('transactionID');
            $table->index('animalID');
        });

        // Add FKs for adoption (same database, OK to use FK)
        // Detect database driver for Danish's SQL Server
        $driver = DB::connection('danish')->getDriverName();

        Schema::connection('danish')->table('adoption', function (Blueprint $table) use ($driver) {
            $table->foreign('bookingID')
                ->references('id')
                ->on('booking')
                ->onDelete('cascade');

            // Use NO ACTION for SQL Server to avoid multiple cascade paths
            if ($driver === 'sqlsrv') {
                $table->foreign('transactionID')
                    ->references('id')
                    ->on('transaction')
                    ->onDelete('no action');
            } else {
                $table->foreign('transactionID')
                    ->references('id')
                    ->on('transaction')
                    ->onDelete('set null');
            }
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
        // Danish's adoption table
        Schema::connection('danish')->table('adoption', function (Blueprint $table) {
            $table->dropForeign(['bookingID']);
            $table->dropForeign(['transactionID']);
        });

        // Atiqah's inventory table
        Schema::connection('atiqah')->table('inventory', function (Blueprint $table) {
            $table->dropForeign(['slotID']);
            $table->dropForeign(['categoryID']);
        });

        // Shafiqah's medical table
        Schema::connection('shafiqah')->table('medical', function (Blueprint $table) {
            $table->dropForeign(['vetID']);
            $table->dropForeign(['animalID']);
        });

        /**
         * Drop tables
         */
        // Danish's tables
        Schema::connection('danish')->dropIfExists('adoption');
        Schema::connection('danish')->dropIfExists('transaction');
        Schema::connection('danish')->dropIfExists('booking');

        // Atiqah's tables
        Schema::connection('atiqah')->dropIfExists('inventory');
        Schema::connection('atiqah')->dropIfExists('category');

        // Shafiqah's table
        Schema::connection('shafiqah')->dropIfExists('medical');
    }
};
