<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalProfile extends Model
{
    protected $table = 'AnimalProfile';
    protected $fillable = 'AdopterProfile';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
