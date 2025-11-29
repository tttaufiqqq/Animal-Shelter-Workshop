<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class AnimalProfile extends Model
{
    protected $connection = 'mysql_device3';

    protected $table = 'animal_profile';
    protected $fillable = [
        'animal_id',
        'age',
        'size',
        'energy_level',
        'good_with_kids',
        'good_with_pets',
        'temperament',
        'medical_needs',
        'animalID'
    ];

   public function animal()
    {
        return $this->belongsTo(Animal::class, 'animalID');
    }
}
