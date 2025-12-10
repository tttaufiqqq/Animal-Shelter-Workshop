<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Danish's database)
    protected $connection = 'danish';

    protected $table = 'booking';

    protected $fillable = [
        'appointment_date',
        'appointment_time',
        'status',
        'remarks',
        'userID',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
    ];

    /**
     * CROSS-DATABASE: Relationship to User model (Taufiq's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function user()
    {
        return $this->setConnection('taufiq')
            ->belongsTo(User::class, 'userID', 'id');
    }

    /**
     * Relationship to AnimalBooking pivot records (same database - danish)
     */
    public function animalBookings()
    {
        return $this->hasMany(AnimalBooking::class, 'bookingID', 'id');
    }

    /**
     * CROSS-DATABASE: Many-to-many relationship to Animals (Shafiqah's database)
     * Through animal_booking pivot table
     */
    public function animals()
    {
        return $this->setConnection('shafiqah')
            ->belongsToMany(Animal::class, 'animal_booking', 'bookingID', 'animalID')
            ->withPivot('remarks')
            ->withTimestamps();
    }

    /**
     * Relationship to Adoptions (same database - danish)
     */
    public function adoptions()
    {
        return $this->hasMany(Adoption::class, 'bookingID', 'id');
    }
}
