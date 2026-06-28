<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnimalBooking extends Pivot
{
    use HasFactory;

    // Specify the database connection for this pivot model (Danish's database)
    protected $connection = 'booking';

    protected $table = 'animal_booking';

    // Increment IDs for pivot table
    public $incrementing = true;

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
        return $this->setConnection('animals')
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
