<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rescue extends Model
{
    protected $fillable = ['date', 'status', 'reportID', 'caretakerID'];

    public function report()
    {
        return $this->belongsTo(Report::class, 'reportID');
    }

    public function caretaker()
    {
        return $this->belongsTo(User::class, 'caretakerID');
    }

    public function animals()
    {
        return $this->hasMany(Animal::class, 'rescueID');
    }
}
