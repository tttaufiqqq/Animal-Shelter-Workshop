<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = ['name', 'address', 'contactNum'];

    public function vets()
    {
        return $this->hasMany(Vet::class, 'clinicID');
    }
}
