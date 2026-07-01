<?php

namespace App\Services;

use App\Services\Concerns\ReportingProcedure\ManagesImageProcedures;
use App\Services\Concerns\ReportingProcedure\ManagesReportProcedures;
use App\Services\Concerns\ReportingProcedure\ManagesRescueProcedures;
use App\Services\Concerns\ReportingProcedure\ReportingAuditHelpers;

class ReportingProcedureService
{
    use ReportingAuditHelpers,
        ManagesReportProcedures,
        ManagesRescueProcedures,
        ManagesImageProcedures;
}
