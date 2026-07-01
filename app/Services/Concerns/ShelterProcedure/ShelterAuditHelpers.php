<?php

namespace App\Services\Concerns\ShelterProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ShelterAuditHelpers
{
    protected function setAuditContext(): void
    {
        $user = Auth::user();

        DB::connection('shelter')->statement('SET @audit_user_id = ?', [$user->id ?? null]);
        DB::connection('shelter')->statement('SET @audit_user_name = ?', [$user->name ?? null]);
        DB::connection('shelter')->statement('SET @audit_user_email = ?', [$user->email ?? null]);
        DB::connection('shelter')->statement('SET @audit_user_role = ?', [$user ? $user->getRoleNames()->first() : null]);
    }
}
