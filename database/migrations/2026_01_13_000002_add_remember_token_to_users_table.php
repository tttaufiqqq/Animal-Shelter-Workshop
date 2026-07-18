<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Standard Laravel/Breeze column (App\Models\User already lists it in
     * $hidden) — missing from the original users table migration. Without
     * it, NewPasswordController's "remember me" token write on password
     * reset fails with "column remember_token does not exist".
     */
    public function up(): void
    {
        Schema::connection('users')->table('users', function (Blueprint $table) {
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('users')->table('users', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
