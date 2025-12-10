<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Specify the database connection for this model (Taufiq's database)
    protected $connection = 'taufiq';
}
