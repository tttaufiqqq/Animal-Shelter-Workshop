<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'booking';
    protected $fillable = ['appointment_date', 'appointment_time', 'status', 'animalID', 'userID'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function animalBookings()
    {
        return $this->hasMany(AnimalBooking::class, 'bookingID', 'id');
    }

    public function animals()
    {
        return $this->belongsToMany(Animal::class, 'AnimalBooking', 'bookingID', 'animalID')
                    ->withPivot('remarks')
                    ->withTimestamps();
    }

    public function adoption()
    {
        return $this->hasMany(Adoption::class, 'bookingID');
    }
}

