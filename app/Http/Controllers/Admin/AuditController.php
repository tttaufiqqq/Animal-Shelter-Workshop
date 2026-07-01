<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\Audit\ViewsAuditLogs;
use App\Http\Controllers\Concerns\Audit\ExportsAuditLogs;
use App\Http\Controllers\Concerns\Audit\DetectsSuspiciousUsers;

class AuditController extends Controller
{
    use ViewsAuditLogs, ExportsAuditLogs, DetectsSuspiciousUsers;
}
