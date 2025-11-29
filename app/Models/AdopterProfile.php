<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class AdopterProfile extends Model
{
      protected $connection = 'pgsql_remote';
    protected $table = 'adopter_profile';
    protected $fillable = [
        'user_id',
        'housing_type',
        'has_children',
        'has_other_pets',
        'activity_level',
        'experience',
        'preferred_species',
        'preferred_size',
        'adopterID'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
