<?php

namespace App\Http\Controllers;

use App\DatabaseErrorHandler;
use App\Services\UserProcedureService;
use App\Http\Controllers\Concerns\Profile\ManagesProfile;
use App\Http\Controllers\Concerns\Profile\ManagesPassword;

class ProfileController extends Controller
{
    use DatabaseErrorHandler, ManagesProfile, ManagesPassword;

    protected UserProcedureService $taufiqService;

    public function __construct(UserProcedureService $taufiqService)
    {
        $this->taufiqService = $taufiqService;
    }
}
