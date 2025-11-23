<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adoption extends Model
{
    protected $table = 'adoption';
    protected $fillable = ['fee', 'remarks', 'bookingID', 'transactionID'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID');
    }

    public function adoptions()
    {
        return $this->hasMany(Adoption::class, 'transactionID');
    }
}
