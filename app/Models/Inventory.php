<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Atiqah's database)
    protected $connection = 'atiqah';

    protected $table = 'inventory';

    protected $fillable = [
        'item_name',
        'quantity',
        'brand',
        'weight',
        'status',
        'slotID',
        'categoryID',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:2',
    ];

    /**
     * Relationship to Slot model (same database - atiqah)
     */
    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slotID', 'id');
    }

    /**
     * Relationship to Category model (same database - atiqah)
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryID', 'id');
    }
}
