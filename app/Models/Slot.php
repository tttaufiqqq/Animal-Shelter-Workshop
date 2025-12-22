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
     *
     * IMPORTANT: Do NOT use $this->setConnection() as it changes the model's
     * connection permanently! The Animal model already specifies its connection
     * as 'shafiqah', so Laravel will query from the correct database.
     */
    public function animals()
    {
        return $this->hasMany(Animal::class, 'slotID', 'id');
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
