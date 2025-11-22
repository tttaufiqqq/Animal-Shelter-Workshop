<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalBooking extends Model
{
    protected $table = 'animal_booking'; // custom table name

    protected $primaryKey = 'id'; // default but OK to declare

    protected $fillable = [
        'bookingID',
        'animalID',
        'remarks',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animalID', 'id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'id');
    }
}

