<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Adoption extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Danish's database)
    protected $connection = 'danish';

    protected $table = 'adoption';

    protected $fillable = [
        'fee',
        'remarks',
        'bookingID',
        'transactionID'
    ];

    protected $casts = [
        'fee' => 'decimal:2',
    ];

    /**
     * Relationship to Booking model (same database - danish)
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'id');
    }

    /**
     * Relationship to Transaction model (same database - danish)
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transactionID', 'id');
    }
}
