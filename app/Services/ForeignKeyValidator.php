<?php

namespace App\Services;

use App\Services\Concerns\ForeignKeyValidator\ValidatesUserEntities;
use App\Services\Concerns\ForeignKeyValidator\ValidatesReportingEntities;
use App\Services\Concerns\ForeignKeyValidator\ValidatesShelterEntities;
use App\Services\Concerns\ForeignKeyValidator\ValidatesAnimalEntities;
use App\Services\Concerns\ForeignKeyValidator\ValidatesBookingEntities;
use App\Services\Concerns\ForeignKeyValidator\ValidatesBatchAndCache;

class ForeignKeyValidator
{
    const CACHE_DURATION = 300;

    use ValidatesUserEntities,
        ValidatesReportingEntities,
        ValidatesShelterEntities,
        ValidatesAnimalEntities,
        ValidatesBookingEntities,
        ValidatesBatchAndCache;
}
