<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return static function (): void {
    Schema::connection('reporting')->create('image', function (Blueprint $table) {
        $table->id();
        $table->string('image_path');
        $table->unsignedBigInteger('animalID')->nullable();  // cross-db logical FK
        $table->unsignedBigInteger('reportID')->nullable();
        $table->unsignedBigInteger('clinicID')->nullable();  // cross-db logical FK
        $table->timestamps();
        $table->index('animalID');
        $table->index('reportID');
        $table->index('clinicID');
    });

    Schema::connection('reporting')->table('image', function (Blueprint $table) {
        $table->foreign('reportID')->references('id')->on('report')->onDelete('cascade');
    });
};
