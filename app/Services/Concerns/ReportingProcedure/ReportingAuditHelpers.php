<?php

namespace App\Services\Concerns\ReportingProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ReportingAuditHelpers
{
    protected function setAuditContext(): void
    {
        $user = Auth::user();

        DB::connection('reporting')->statement('SET @audit_user_id = ?', [$user->id ?? null]);
        DB::connection('reporting')->statement('SET @audit_user_name = ?', [$user->name ?? null]);
        DB::connection('reporting')->statement('SET @audit_user_email = ?', [$user->email ?? null]);
        DB::connection('reporting')->statement('SET @audit_user_role = ?', [$user ? $user->getRoleNames()->first() : null]);
    }
}
