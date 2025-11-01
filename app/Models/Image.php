<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['image_path', 'animalID', 'reportID'];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animalID');
    }

    public function report()
    {
        return $this->belongsTo(Report::class, 'reportID');
    }
}
