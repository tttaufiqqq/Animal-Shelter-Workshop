<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Creates TRUE PostgreSQL PROCEDURES (not functions)
     * that use CALL syntax with OUT parameters.
     */
    public function up(): void
    {
        $conn   = DB::connection('users');
        $sqlDir = database_path('sql/taufiq-users/user_procedures_with_call');

        $conn->unprepared(require "$sqlDir/sp_user_create_proc.php");
        $conn->unprepared(require "$sqlDir/sp_user_update_proc.php");
        $conn->unprepared(require "$sqlDir/sp_user_update_password_proc.php");
        $conn->unprepared(require "$sqlDir/sp_user_delete_proc.php");
        $conn->unprepared(require "$sqlDir/sp_user_suspend_proc.php");
        $conn->unprepared(require "$sqlDir/sp_user_lock_proc.php");
        $conn->unprepared(require "$sqlDir/sp_user_unlock_proc.php");
        $conn->unprepared(require "$sqlDir/sp_user_force_password_reset_proc.php");
    }

    public function down(): void
    {
        $conn = DB::connection('users');

        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_user_create_proc');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_user_update_proc');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_user_update_password_proc');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_user_delete_proc');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_user_suspend_proc');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_user_lock_proc');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_user_unlock_proc');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_user_force_password_reset_proc');
    }
};
