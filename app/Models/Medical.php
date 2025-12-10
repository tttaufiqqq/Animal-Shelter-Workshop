<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medical extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Shafiqah's database)
    protected $connection = 'shafiqah';

    protected $table = 'medical';

    protected $fillable = [
        'treatment_type',
        'diagnosis',
        'action',
        'remarks',
        'costs',
        'vetID',
        'animalID',
    ];

    protected $casts = [
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
