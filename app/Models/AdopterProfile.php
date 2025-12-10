<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdopterProfile extends Model
{
    use HasFactory;

    // Specify the database connection for this model
    protected $connection = 'taufiq';

    protected $table = 'adopter_profile';

    protected $fillable = [
        'adopterID',
        'housing_type',
        'has_children',
        'has_other_pets',
        'activity_level',
        'experience',
        'preferred_species',
        'preferred_size',
    ];

    protected $casts = [
        'has_children' => 'boolean',
        'has_other_pets' => 'boolean',
    ];

    /**
     * Relationship to User model (same database - taufiq)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'adopterID', 'id');
    }
}
