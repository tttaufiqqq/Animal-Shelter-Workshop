<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
    protected $fillable = [
        'name', 'type', 'date', 'next_due_date', 'remarks', 'animalID', 'vetID'
    ];

    public function vet()
    {
        return $this->belongsTo(Vet::class, 'vetID');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animalID');
    }
}
