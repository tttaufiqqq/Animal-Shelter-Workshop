<?php

namespace App\Services;

use App\Services\Concerns\DatabaseConnection\ChecksConnections;
use App\Services\Concerns\DatabaseConnection\FiltersConnections;
use App\Services\Concerns\DatabaseConnection\ManagesConnectionCache;

class DatabaseConnectionChecker
{
    const CONNECTIONS = [
        'users' => [
            'name' => 'Taufiq (PostgreSQL)',
            'module' => 'User Management',
            'port' => 5434,
        ],
        'reporting' => [
            'name' => 'Eilya (MySQL)',
            'module' => 'Stray Reporting',
            'port' => 3307,
        ],
        'animals' => [
            'name' => 'Shafiqah (MySQL)',
            'module' => 'Animal Management',
            'port' => 3309,
        ],
        'shelter' => [
            'name' => 'Atiqah (MySQL)',
            'module' => 'Shelter Management',
            'port' => 3308,
        ],
        'booking' => [
            'name' => 'Danish (SQL Server)',
            'module' => 'Booking & Adoption',
            'port' => 1434,
        ],
    ];

    use ChecksConnections,
        FiltersConnections,
        ManagesConnectionCache;
}
