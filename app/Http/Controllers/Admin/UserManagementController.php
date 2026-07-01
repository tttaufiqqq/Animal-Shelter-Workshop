<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserProcedureService;
use App\Http\Controllers\Concerns\UserManagement\ManagesUserAccounts;
use App\Http\Controllers\Concerns\UserManagement\MonitorsUserActivity;

class UserManagementController extends Controller
{
    use ManagesUserAccounts, MonitorsUserActivity;

    protected UserProcedureService $taufiqService;

    public function __construct(UserProcedureService $taufiqService)
    {
        $this->taufiqService = $taufiqService;
    }
}
