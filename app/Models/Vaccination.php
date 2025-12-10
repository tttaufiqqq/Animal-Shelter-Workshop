<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vaccination extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Shafiqah's database)
    protected $connection = 'shafiqah';

    protected $table = 'vaccination';

    protected $fillable = [
        'name',
        'type',
        'next_due_date',
        'remarks',
        'weight',
        'costs',
        'animalID',
        'vetID',
    ];

    protected $casts = [
        'next_due_date' => 'date',
        'weight' => 'decimal:2',
        'costs' => 'decimal:2',
    ];

    /**
     * Relationship to Vet model (same database - shafiqah)
     */
    public function vet()
    {
        return $this->belongsTo(Vet::class, 'vetID', 'id');
    }

    /**
     * Relationship to Animal model (same database - shafiqah)
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animalID', 'id');
    }
}
