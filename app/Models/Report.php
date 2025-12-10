<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Eilya's database)
    protected $connection = 'eilya';

    protected $table = 'report';

    protected $fillable = [
        'latitude',
        'longitude',
        'address',
        'city',
        'state',
        'report_status',
        'description',
        'userID',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
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
     * Relationship to Images (same database - eilya)
     */
    public function images()
    {
        return $this->hasMany(Image::class, 'reportID', 'id');
    }

    /**
     * Relationship to Rescue record (same database - eilya)
     */
    public function rescue()
    {
        return $this->hasOne(Rescue::class, 'reportID', 'id');
    }
}
