<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
      protected $connection = 'mysql_device_1';

    protected $table = 'image';
    protected $fillable = ['image_path', 'animalID', 'reportID', 'clinicID'];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animalID');
    }

    public function report()
    {
        return $this->belongsTo(Report::class, 'reportID');
    }

     public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinicID');
    }
}
