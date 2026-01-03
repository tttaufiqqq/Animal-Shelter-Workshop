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
        Schema::connection('shafiqah')->table('animal', function (Blueprint $table) {
            // Add index on adoption_status for faster filtering in animal matching queries
            $table->index('adoption_status', 'idx_animal_adoption_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('shafiqah')->table('animal', function (Blueprint $table) {
            $table->dropIndex('idx_animal_adoption_status');
        });
    }
};
