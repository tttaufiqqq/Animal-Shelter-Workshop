<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slot extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Atiqah's database)
    protected $connection = 'atiqah';

    protected $table = 'slot';

    protected $fillable = [
        'name',
        'capacity',
        'status',
        'sectionID',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * CROSS-DATABASE: Relationship to Animals (Shafiqah's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function animals()
    {
        return $this->setConnection('shafiqah')
            ->hasMany(Animal::class, 'slotID', 'id');
    }

    /**
     * Relationship to Inventory items (same database - atiqah)
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'slotID', 'id');
    }

    /**
     * Relationship to Section (same database - atiqah)
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'sectionID', 'id');
    }
}
