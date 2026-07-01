<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return static function (): void {
    Schema::connection('shelter')->create('section', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->text('description')->nullable();
        $table->timestamps();
    });

    Schema::connection('shelter')->create('slot', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->integer('capacity')->nullable();
        $table->string('status')->nullable();
        $table->unsignedBigInteger('sectionID')->nullable();
        $table->timestamps();
        $table->index('sectionID');
    });

    Schema::connection('shelter')->table('slot', function (Blueprint $table) {
        $table->foreign('sectionID')
            ->references('id')
            ->on('section')
            ->onDelete('set null');
    });
};
