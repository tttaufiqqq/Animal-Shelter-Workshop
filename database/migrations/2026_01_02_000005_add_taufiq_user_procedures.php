<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('users');
        $sqlDir = database_path('sql/taufiq-users/user_procedures');

        $conn->unprepared(require "$sqlDir/sp_user_create.php");
        $conn->unprepared(require "$sqlDir/sp_user_read.php");
        $conn->unprepared(require "$sqlDir/sp_user_update.php");
        $conn->unprepared(require "$sqlDir/sp_user_update_password.php");
        $conn->unprepared(require "$sqlDir/sp_user_delete.php");
        $conn->unprepared(require "$sqlDir/sp_user_suspend.php");
        $conn->unprepared(require "$sqlDir/sp_user_lock.php");
        $conn->unprepared(require "$sqlDir/sp_user_unlock.php");
        $conn->unprepared(require "$sqlDir/sp_user_force_password_reset.php");
    }

    public function down(): void
    {
        $conn = DB::connection('users');

        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_create');
        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_read');
        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_update');
        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_update_password');
        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_delete');
        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_suspend');
        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_lock');
        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_unlock');
        $conn->unprepared('DROP FUNCTION IF EXISTS sp_user_force_password_reset');
    }
};
