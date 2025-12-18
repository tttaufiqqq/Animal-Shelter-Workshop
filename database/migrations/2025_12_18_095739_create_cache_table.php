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
        // Create cache table in taufiq database (PostgreSQL)
        Schema::connection('taufiq')->create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration');
        });

        // Create cache_locks table for atomic locks
        Schema::connection('taufiq')->create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('taufiq')->dropIfExists('cache_locks');
        Schema::connection('taufiq')->dropIfExists('cache');
    }
};
