<?php

namespace App\Http\Controllers;

use App\DatabaseErrorHandler;
use App\Services\ShelterProcedureService;
use App\Http\Controllers\Concerns\ShelterManagement\ManagesSections;
use App\Http\Controllers\Concerns\ShelterManagement\ManagesSlots;
use App\Http\Controllers\Concerns\ShelterManagement\ManagesCategories;
use App\Http\Controllers\Concerns\ShelterManagement\ManagesInventory;
use App\Http\Controllers\Concerns\ShelterManagement\ViewsDetails;

class ShelterManagementController extends Controller
{
    use DatabaseErrorHandler, ManagesSections, ManagesSlots, ManagesCategories, ManagesInventory, ViewsDetails;

    protected $atiqahService;

    public function __construct(ShelterProcedureService $atiqahService)
    {
        $this->atiqahService = $atiqahService;
    }
}
