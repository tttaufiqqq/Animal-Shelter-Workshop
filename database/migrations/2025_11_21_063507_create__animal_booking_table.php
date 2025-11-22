<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('animal_booking', function (Blueprint $table) {
            $table->id();

            // 🚩 FIX THIS LINE: Explicitly reference the 'booking' table
            $table->foreignId('bookingID')->constrained('booking')->onDelete('cascade');

            // 🚩 FIX THIS LINE: Explicitly reference the 'animal' table
            $table->foreignId('animalID')->constrained('animal')->onDelete('cascade');

            $table->text('remarks')->nullable();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_booking');

    }
};
