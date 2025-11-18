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

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animalID', 'id');
    }

    public function adoption()
    {
        return $this->hasOne(Adoption::class, 'bookingID');
    }
}

