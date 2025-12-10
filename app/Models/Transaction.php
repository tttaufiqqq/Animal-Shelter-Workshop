<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Danish's database)
    protected $connection = 'danish';

    protected $table = 'transaction';

    protected $fillable = [
        'amount',
        'status',
        'remarks',
        'reference_no',
        'bill_code',
        'type',
        'userID',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * CROSS-DATABASE: Relationship to User model (Taufiq's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function user()
    {
        return $this->setConnection('taufiq')
            ->belongsTo(User::class, 'userID', 'id');
    }

    /**
     * Relationship to Adoptions (same database - danish)
     */
    public function adoptions()
    {
        return $this->hasMany(Adoption::class, 'transactionID', 'id');
    }
}
