<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Clinic extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Shafiqah's database)
    protected $connection = 'shafiqah';

    protected $table = 'clinic';

    protected $fillable = [
        'name',
        'address',
        'contactNum',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Relationship to Vets (same database - shafiqah)
     */
    public function vets()
    {
        return $this->hasMany(Vet::class, 'clinicID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Images (Eilya's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function images()
    {
        return $this->setConnection('eilya')
            ->hasMany(Image::class, 'clinicID', 'id');
    }
}
