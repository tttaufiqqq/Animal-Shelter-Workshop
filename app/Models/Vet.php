<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vet extends Model
{
      protected $connection = 'mysql_device_3';

    protected $table = 'vet';
    protected $fillable = [
        'name', 'email', 'contactNum', 'specialization', 'license_no', 'clinicID'
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinicID');
    }

    public function medicals()
    {
        return $this->hasMany(Medical::class, 'vetID');
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'vetID');
    }
}
