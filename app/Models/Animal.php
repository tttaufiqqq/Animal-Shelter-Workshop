<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $table = 'animal';
    protected $fillable = [
        'species', 'name', 'health_details', 'age', 'gender',
        'adoption_status', 'rescueID', 'slotID', 'vaccinationID'
    ];

    public function rescue()
    {
        return $this->belongsTo(Rescue::class, 'rescueID');
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slotID');
    }
    public function medicals()
    {
        return $this->hasMany(Medical::class, 'animalID');
    }
    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'animalID');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'animalID');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'animalID', 'id');
    }
}

