<?php

namespace App\Services;

use App\Services\Concerns\Audit\LogsCore;
use App\Services\Concerns\Audit\LogsDomainEvents;

class AuditService
{
    use LogsCore, LogsDomainEvents;

    /**
     * Team member IP address mapping (from SSH tunnels setup)
     */
    protected static $teamIpMapping = [
        'reporting' => '10.18.26.14',
        'shelter' => '10.18.26.84',
        'piqa' => '10.18.26.121',
        'animals' => '10.18.26.121',
        'booking' => '10.18.26.18',
        'users' => '10.18.26.156',
    ];
}
