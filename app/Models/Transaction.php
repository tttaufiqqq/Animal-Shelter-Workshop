<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
      protected $connection = 'sqlsrv_remote';

    protected $table = 'transaction';
   protected $fillable = [
        'amount', 'status', 'remarks', 'reference_no', 'bill_code', 'type', 'userID'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function adoptions()
    {
        return $this->hasMany(Adoption::class, 'transactionID');
    }
}
