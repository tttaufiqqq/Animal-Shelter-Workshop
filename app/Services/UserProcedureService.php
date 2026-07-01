<?php

namespace App\Services;

use App\Services\Concerns\UserProcedure\ManagesAdopterProfiles;
use App\Services\Concerns\UserProcedure\ManagesUserCrud;
use App\Services\Concerns\UserProcedure\ManagesUserRoles;
use App\Services\Concerns\UserProcedure\ManagesUserSecurity;
use App\Services\Concerns\UserProcedure\UserAnalyticsAndMaintenance;
use App\Services\Concerns\UserProcedure\UserProcedureHelpers;

class UserProcedureService
{
    use UserProcedureHelpers,
        ManagesUserCrud,
        ManagesUserSecurity,
        ManagesAdopterProfiles,
        ManagesUserRoles,
        UserAnalyticsAndMaintenance;
}
