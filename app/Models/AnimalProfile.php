<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnimalProfile extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Shafiqah's database)
    protected $connection = 'shafiqah';

    protected $table = 'animal_profile';

    protected $fillable = [
        'animalID',
        'age',
        'size',
        'energy_level',
        'good_with_kids',
        'good_with_pets',
        'temperament',
        'medical_needs',
    ];

    protected $casts = [
        'good_with_kids' => 'boolean',
        'good_with_pets' => 'boolean',
    ];

    /**
     * Relationship to Animal model (same database - shafiqah)
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animalID', 'id');
    }
}
