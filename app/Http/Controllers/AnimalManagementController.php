<?php

namespace App\Http\Controllers;

use App\DatabaseErrorHandler;
use App\Services\AnimalProcedureService;
use App\Http\Controllers\Concerns\AnimalManagement\ManagesMatching;
use App\Http\Controllers\Concerns\AnimalManagement\ManagesAnimals;
use App\Http\Controllers\Concerns\AnimalManagement\ViewsAnimals;
use App\Http\Controllers\Concerns\AnimalManagement\ManagesClinicsVets;
use App\Http\Controllers\Concerns\AnimalManagement\ManagesMedicalRecords;

class AnimalManagementController extends Controller
{
    use DatabaseErrorHandler, ManagesMatching, ManagesAnimals, ViewsAnimals, ManagesClinicsVets, ManagesMedicalRecords;

    protected $procedureService;

    public function __construct(AnimalProcedureService $procedureService)
    {
        $this->procedureService = $procedureService;
    }
}
