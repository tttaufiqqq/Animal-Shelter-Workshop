<?php

namespace App\Services;

use App\Services\Concerns\ShelterProcedure\ManagesCategoryProcedures;
use App\Services\Concerns\ShelterProcedure\ManagesInventoryProcedures;
use App\Services\Concerns\ShelterProcedure\ManagesSectionProcedures;
use App\Services\Concerns\ShelterProcedure\ManagesSlotProcedures;
use App\Services\Concerns\ShelterProcedure\ShelterAuditHelpers;

class ShelterProcedureService
{
    use ShelterAuditHelpers,
        ManagesSectionProcedures,
        ManagesSlotProcedures,
        ManagesCategoryProcedures,
        ManagesInventoryProcedures;
}
