<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $connection = 'mysql_device_2';

    protected $table = 'section';
    protected $fillable = ['name', 'description'];
    public function slots(){
        return $this->hasMany(Slot::class,'sectionID');
    }
}
