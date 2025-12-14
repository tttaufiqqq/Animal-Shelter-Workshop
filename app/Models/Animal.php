<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Animal extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Shafiqah's database)
    protected $connection = 'shafiqah';

    protected $table = 'animal';

    protected $fillable = [
        'species',
        'name',
        'health_details',
        'age',
        'gender',
        'weight',
        'adoption_status',
        'rescueID',
        'slotID',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    /**
     * Relationship to AnimalProfile model (same database - shafiqah)
     */
    public function profile()
    {
        return $this->hasOne(AnimalProfile::class, 'animalID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Rescue model (Eilya's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function rescue()
    {
        return $this->setConnection('eilya')
            ->belongsTo(Rescue::class, 'rescueID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Slot model (Atiqah's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function slot()
    {
        return $this->setConnection('atiqah')
            ->belongsTo(Slot::class, 'slotID', 'id');
    }

    /**
     * Relationship to Medical records (same database - shafiqah)
     */
    public function medicals()
    {
        return $this->hasMany(Medical::class, 'animalID', 'id');
    }

    /**
     * Relationship to Vaccinations (same database - shafiqah)
     */
    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'animalID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Images (Eilya's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function images()
    {
        return $this->setConnection('eilya')
            ->hasMany(Image::class, 'animalID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to AnimalBooking (Danish's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function animalBookings()
    {
        return $this->setConnection('danish')
            ->hasMany(AnimalBooking::class, 'animalID', 'id');
    }

    /**
     * CROSS-DATABASE: Many-to-many relationship to Bookings (Danish's database)
     * Through animal_booking pivot table (on danish database)
     * Uses custom pivot model to specify the correct database connection
     */
    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'animal_booking', 'animalID', 'bookingID')
            ->using(AnimalBooking::class)
            ->withPivot('remarks')
            ->withTimestamps();
    }

    /**
     * CROSS-DATABASE: Many-to-many relationship to VisitLists (Danish's database)
     * Through visit_list_animal pivot table (on danish database)
     * Uses custom pivot model to specify the correct database connection
     */
    public function visitLists()
    {
        return $this->belongsToMany(VisitList::class, 'visit_list_animal', 'animalID', 'listID')
            ->using(VisitListAnimal::class)
            ->withPivot('remarks')
            ->withTimestamps();
    }
}
