<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['appointment_date', 'status', 'animalID', 'userID'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function animals()
    {
        return $this->belongsToMany(Animal::class, 'animal_booking', 'bookingID', 'animalID');
    }

    public function adoption()
    {
        return $this->hasOne(Adoption::class, 'bookingID');
    }
}

