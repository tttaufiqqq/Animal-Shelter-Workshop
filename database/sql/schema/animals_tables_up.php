<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return static function (): void {
    Schema::connection('animals')->create('clinic', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('address')->nullable();
        $table->string('contactNum', 30)->nullable();
        $table->decimal('latitude', 10, 8)->nullable();
        $table->decimal('longitude', 11, 8)->nullable();
        $table->timestamps();
    });

    Schema::connection('animals')->create('vet', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('contactNum', 30)->nullable();
        $table->string('specialization')->nullable();
        $table->string('license_no', 50)->nullable();
        $table->unsignedBigInteger('clinicID')->nullable();
        $table->timestamps();
        $table->index('clinicID');
    });

    Schema::connection('animals')->table('vet', function (Blueprint $table) {
        $table->foreign('clinicID')->references('id')->on('clinic')->onDelete('set null');
    });

    Schema::connection('animals')->create('animal', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('species')->nullable();
        $table->text('health_details')->nullable();
        $table->string('age')->nullable();
        $table->decimal('weight', 8, 2)->nullable();
        $table->enum('gender', ['Male', 'Female', 'Unknown'])->default('Unknown');
        $table->string('adoption_status')->nullable();
        $table->unsignedBigInteger('rescueID')->nullable(); // cross-db logical FK
        $table->unsignedBigInteger('slotID')->nullable();   // cross-db logical FK
        $table->timestamps();
        $table->index('rescueID');
        $table->index('slotID');
    });

    Schema::connection('animals')->create('vaccination', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('type')->nullable();
        $table->date('next_due_date')->nullable();
        $table->text('remarks')->nullable();
        $table->decimal('weight', 8, 2)->nullable();
        $table->decimal('costs', 10, 2)->nullable();
        $table->unsignedBigInteger('animalID')->nullable();
        $table->unsignedBigInteger('vetID')->nullable();
        $table->timestamps();
        $table->index('animalID');
        $table->index('vetID');
    });

    Schema::connection('animals')->table('vaccination', function (Blueprint $table) {
        $table->foreign('animalID')->references('id')->on('animal')->onDelete('set null');
        $table->foreign('vetID')->references('id')->on('vet')->onDelete('set null');
    });
};
