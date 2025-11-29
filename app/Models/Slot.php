<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
      protected $connection = 'mysql_device_2';

    protected $table = 'slot';
    protected $fillable = ['name', 'section', 'capacity', 'status', 'sectionID'];

    public function animals()
    {
        return $this->hasMany(Animal::class, 'slotID');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'slotID');
    }

    public function section(){
        return $this->belongsTo(Section::class, 'sectionID');
    }
}
