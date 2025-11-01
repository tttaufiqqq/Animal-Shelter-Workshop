<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
   protected $fillable = [
        'amount', 'status', 'remarks', 'date', 'type', 'userID'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function adoption()
    {
        return $this->hasOne(Adoption::class, 'transactionID');
    }
}
