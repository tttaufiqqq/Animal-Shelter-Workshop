<?php

namespace App\Http\Controllers;

use App\DatabaseErrorHandler;
use App\Services\ReportingProcedureService;
use App\Http\Controllers\Concerns\StrayReporting\ManagesReports;
use App\Http\Controllers\Concerns\StrayReporting\ManagesRescues;
use App\Http\Controllers\Concerns\StrayReporting\UpdatesStatusWithAnimals;

class StrayReportingManagementController extends Controller
{
    use DatabaseErrorHandler, ManagesReports, ManagesRescues, UpdatesStatusWithAnimals;

    protected $procedureService;

    public function __construct(ReportingProcedureService $procedureService)
    {
        $this->procedureService = $procedureService;
    }
}
