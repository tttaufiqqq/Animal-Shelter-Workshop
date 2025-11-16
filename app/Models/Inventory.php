<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $fillable = [
        'item_name', 'quantity', 'brand', 'weight', 'status', 'slotID', 'categoryID'
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slotID');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryID');
    }
}
