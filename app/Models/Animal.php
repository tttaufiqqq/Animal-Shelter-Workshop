<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $table = 'animal';
    protected $fillable = [
        'species', 'name', 'health_details', 'age', 'gender', 'weight',
        'adoption_status', 'rescueID', 'slotID', 'vaccinationID'
    ];

    public function profile()
    {
        return $this->hasOne(AnimalProfile::class, 'animalID', 'id');
    }

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

    public function animalBookings()
    {
        return $this->hasMany(AnimalBooking::class, 'animalID', 'id');
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'AnimalBooking', 'animalID', 'bookingID')
                    ->withPivot('remarks')
                    ->withTimestamps();
    }

    public function visitLists()
    {
        return $this->belongsToMany(VisitList::class, 'visit_list_animals', 'animalID', 'visit_list_id')
            ->withPivot('remarks')
            ->withTimestamps();
    }


}

