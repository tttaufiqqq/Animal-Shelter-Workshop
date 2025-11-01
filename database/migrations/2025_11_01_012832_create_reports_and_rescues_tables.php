<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Create tables first (without foreign keys)
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('report_status')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('userID')->nullable(); // FK added later
            $table->timestamps();
        });

        Schema::create('rescues', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('status')->nullable();
            $table->unsignedBigInteger('reportID')->nullable();   // FK added later
            $table->unsignedBigInteger('caretakerID')->nullable(); // FK added later
            $table->timestamps();
        });

        // Step 2: Add foreign key constraints (after tables exist)
        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('userID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        Schema::table('rescues', function (Blueprint $table) {
            $table->foreign('reportID')
                  ->references('id')
                  ->on('reports')
                  ->onDelete('cascade');

            $table->foreign('caretakerID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Drop foreign keys first
        Schema::table('rescues', function (Blueprint $table) {
            $table->dropForeign(['reportID']);
            $table->dropForeign(['caretakerID']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['userID']);
        });

        // Then drop the tables
        Schema::dropIfExists('rescues');
        Schema::dropIfExists('reports');
    }
};
