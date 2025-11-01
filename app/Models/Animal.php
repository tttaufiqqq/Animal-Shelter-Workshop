<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{

    protected $fillable = [
        'species', 'health_details', 'age', 'gender',
        'adoption_status', 'arrival_date', 'medical_status',
        'rescueID', 'slotID', 'vaccinationID'
    ];

    public function rescue()
    {
        return $this->belongsTo(Rescue::class, 'rescueID');
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slotID');
    }

    public function vaccination()
    {
        return $this->belongsTo(Vaccination::class, 'vaccinationID');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'animalID');
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'animal_booking', 'animalID', 'bookingID');
    }
}

