<?php

namespace App\Services;

use App\Services\Concerns\AnimalProcedure\AnimalAuditHelpers;
use App\Services\Concerns\AnimalProcedure\ManagesClinicProcedures;
use App\Services\Concerns\AnimalProcedure\ManagesVetProcedures;
use App\Services\Concerns\AnimalProcedure\ManagesAnimalProcedures;
use App\Services\Concerns\AnimalProcedure\ManagesMedicalProcedures;
use App\Services\Concerns\AnimalProcedure\ManagesProfileProcedures;

class AnimalProcedureService
{
    use AnimalAuditHelpers,
        ManagesClinicProcedures,
        ManagesVetProcedures,
        ManagesAnimalProcedures,
        ManagesMedicalProcedures,
        ManagesProfileProcedures;
}
