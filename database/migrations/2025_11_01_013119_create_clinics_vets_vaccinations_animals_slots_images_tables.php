<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schemaDir = database_path('sql/schema');

        (require "$schemaDir/shelter_tables_up.php")();
        (require "$schemaDir/animals_tables_up.php")();
        (require "$schemaDir/reporting_tables_up.php")();
    }

    public function down(): void
    {
        // Drop FKs first
        Schema::connection('reporting')->table('image', function (Blueprint $table) {
            $table->dropForeign(['reportID']);
        });

        Schema::connection('animals')->table('vaccination', function (Blueprint $table) {
            $table->dropForeign(['animalID']);
            $table->dropForeign(['vetID']);
        });

        Schema::connection('animals')->table('vet', function (Blueprint $table) {
            $table->dropForeign(['clinicID']);
        });

        Schema::connection('shelter')->table('slot', function (Blueprint $table) {
            $table->dropForeign(['sectionID']);
        });

        // Then drop tables
        Schema::connection('reporting')->dropIfExists('image');

        Schema::connection('animals')->dropIfExists('vaccination');
        Schema::connection('animals')->dropIfExists('animal');
        Schema::connection('animals')->dropIfExists('vet');
        Schema::connection('animals')->dropIfExists('clinic');

        Schema::connection('shelter')->dropIfExists('slot');
        Schema::connection('shelter')->dropIfExists('section');
    }
};
