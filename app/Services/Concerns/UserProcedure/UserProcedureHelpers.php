<?php

namespace App\Services\Concerns\UserProcedure;

use Illuminate\Support\Facades\Auth;

trait UserProcedureHelpers
{
    protected function getAuditContext(): array
    {
        $user = Auth::user();

        return [
            'user_id' => $user->id ?? null,
            'user_name' => $user->name ?? null,
            'user_email' => $user->email ?? null,
            'user_role' => $user ? $user->getRoleNames()->first() : null,
        ];
    }
}
