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
        Schema::create('visit_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userID')->unique(); // one visit list per user
            $table->timestamps();

            $table->foreign('userID')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('visit_list_animal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listID');
            $table->unsignedBigInteger('animalID');
            $table->text('remarks')->nullable()->after('animalID');
            $table->timestamps();

            $table->foreign('listID')->references('id')->on('visit_list')->onDelete('cascade');
            $table->foreign('animalID')->references('id')->on('animal')->onDelete('cascade');

            // Prevent duplicate animals per visit list
            $table->unique(['listID', 'animalID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_list');
        Schema::dropIfExists('visit_list_animal');
    }
};
