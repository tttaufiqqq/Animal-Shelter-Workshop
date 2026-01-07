<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add unique constraint to adopterID in adopter_profile table (Taufiq database)
     * This is required for the ON CONFLICT clause in the upsert stored procedure
     */
    public function up(): void
    {
        Schema::connection('taufiq')->table('adopter_profile', function (Blueprint $table) {
            // Drop the existing index
            $table->dropIndex(['adopterID']);

            // Add unique constraint (each adopter can only have one profile)
            $table->unique('adopterID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('taufiq')->table('adopter_profile', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['adopterID']);

            // Restore the regular index
            $table->index('adopterID');
        });
    }
};
