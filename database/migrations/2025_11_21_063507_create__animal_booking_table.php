<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This table belongs to Danish - Booking Adoption
     */
    public function up(): void
    {
        Schema::connection('booking')->create('animal_booking', function (Blueprint $table) {
            $table->id();

            // FK to booking table (same database, OK to use FK)
            $table->unsignedBigInteger('bookingID');

            // Logical FK - references Shafiqah - Stray Animal animal table (cross-database, NO FK constraint)
            $table->unsignedBigInteger('animalID');

            $table->text('remarks')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('bookingID');
            $table->index('animalID');
        });

        // Add FK for bookingID only (same database, OK to use FK)
        // Do NOT add FK for animalID (cross-database to Shafiqah - Stray Animal)
        Schema::connection('booking')->table('animal_booking', function (Blueprint $table) {
            $table->foreign('bookingID')
                ->references('id')
                ->on('booking')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK first
        Schema::connection('booking')->table('animal_booking', function (Blueprint $table) {
            $table->dropForeign(['bookingID']);
        });

        Schema::connection('booking')->dropIfExists('animal_booking');
    }
};
