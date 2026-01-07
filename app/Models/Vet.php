<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vet extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Shafiqah's database)
    protected $connection = 'shafiqah';

    protected $table = 'vet';

    protected $fillable = [
        'name',
        'email',
        'contactNum',
        'specialization',
        'license_no',
        'clinicID',
    ];

    /**
     * Relationship to Clinic model (same database - shafiqah)
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinicID', 'id');
    }

    /**
     * Relationship to Medical records (same database - shafiqah)
     */
    public function medicals()
    {
        return $this->hasMany(Medical::class, 'vetID', 'id');
    }

    /**
     * Relationship to Vaccinations (same database - shafiqah)
     */
    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'vetID', 'id');
    }
}
