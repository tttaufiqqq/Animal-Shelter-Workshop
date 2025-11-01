<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adoption extends Model
{
    protected $fillable = ['fee', 'remarks', 'bookingID', 'transactionID'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transactionID');
    }
}
