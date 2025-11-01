<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $fillable = ['name', 'section', 'capacity', 'status'];

    public function animals()
    {
        return $this->hasMany(Animal::class, 'slotID');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'slotID');
    }
}
