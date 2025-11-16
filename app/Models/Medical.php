<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medical extends Model
{
    protected $table = 'medical';
    protected $fillable = [
        'treatment_type', 'diagnosis', 'action', 'remarks',
        'costs', 'vetID', 'animalID'
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
