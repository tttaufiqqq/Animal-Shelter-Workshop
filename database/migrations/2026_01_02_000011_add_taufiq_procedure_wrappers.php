<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Creates WRAPPER FUNCTIONS that call TRUE PROCEDURES
     * and return their OUT parameters as TABLE results (for Laravel compatibility).
     */
    public function up(): void
    {
        $conn   = DB::connection('users');
        $sqlDir = database_path('sql/taufiq/procedure_wrappers');

        $conn->unprepared(require "$sqlDir/fn_user_create.php");
        $conn->unprepared(require "$sqlDir/fn_user_update.php");
        $conn->unprepared(require "$sqlDir/fn_user_update_password.php");
        $conn->unprepared(require "$sqlDir/fn_user_delete.php");
        $conn->unprepared(require "$sqlDir/fn_user_suspend.php");
        $conn->unprepared(require "$sqlDir/fn_user_lock.php");
        $conn->unprepared(require "$sqlDir/fn_user_unlock.php");
        $conn->unprepared(require "$sqlDir/fn_user_force_password_reset.php");
        $conn->unprepared(require "$sqlDir/fn_adopter_profile_upsert.php");
        $conn->unprepared(require "$sqlDir/fn_adopter_profile_delete.php");
        $conn->unprepared(require "$sqlDir/fn_user_assign_role.php");
        $conn->unprepared(require "$sqlDir/fn_user_revoke_role.php");
    }

    public function down(): void
    {
        $conn = DB::connection('users');

        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_create');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_update');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_update_password');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_delete');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_suspend');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_lock');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_unlock');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_force_password_reset');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_adopter_profile_upsert');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_adopter_profile_delete');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_assign_role');
        $conn->unprepared('DROP FUNCTION IF EXISTS fn_user_revoke_role');
    }
};
