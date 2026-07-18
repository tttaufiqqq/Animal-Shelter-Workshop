<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Standard Laravel table for Illuminate\Support\Facades\Password's
     * broker (see config/auth.php's passwords.users.table) — missing since
     * the original Breeze scaffolding was split into per-connection
     * migrations and this one was dropped. Without it, the "forgot
     * password" flow fails silently: Password::sendResetLink() has nowhere
     * to store the token. Lives on 'users' (Postgres), same as the users
     * and sessions tables it's paired with.
     */
    public function up(): void
    {
        Schema::connection('users')->create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('users')->dropIfExists('password_reset_tokens');
    }
};
