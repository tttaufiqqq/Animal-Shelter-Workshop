<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
    protected $table = 'vaccination';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name', 'type','next_due_date', 'remarks', 'costs', 'animalID', 'vetID'
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
