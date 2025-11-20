<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdopterProfile extends Model
{
    protected $table = 'AdopterProfile';
    protected $fillable = 'AdopterProfile';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
