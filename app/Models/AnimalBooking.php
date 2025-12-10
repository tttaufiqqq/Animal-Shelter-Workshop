<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnimalBooking extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Danish's database)
    protected $connection = 'danish';

    protected $table = 'animal_booking';

    protected $fillable = [
        'bookingID',
        'animalID',
        'remarks',
    ];

    /**
     * CROSS-DATABASE: Relationship to Animal model (Shafiqah's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function animal()
    {
        return $this->setConnection('shafiqah')
            ->belongsTo(Animal::class, 'animalID', 'id');
    }

    /**
     * Relationship to Booking model (same database - danish)
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'id');
    }
}
