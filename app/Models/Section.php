<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Atiqah's database)
    protected $connection = 'atiqah';

    protected $table = 'section';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relationship to Slots (same database - atiqah)
     */
    public function slots()
    {
        return $this->hasMany(Slot::class, 'sectionID', 'id');
    }
}
