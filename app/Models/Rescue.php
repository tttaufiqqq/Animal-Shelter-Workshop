<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rescue extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Eilya's database)
    protected $connection = 'eilya';

    protected $table = 'rescue';

    protected $fillable = [
        'status',
        'remarks',
        'reportID',
        'caretakerID',
    ];

    // Status constants
    const STATUS_SCHEDULED = 'Scheduled';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_SUCCESS = 'Success';
    const STATUS_FAILED = 'Failed';

    /**
     * Relationship to Report model (same database - eilya)
     */
    public function report()
    {
        return $this->belongsTo(Report::class, 'reportID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to User model (Taufiq's database)
     * Caretaker is a user assigned to handle the rescue
     * This is a logical relationship - no database-level foreign key
     */
    public function caretaker()
    {
        return $this->setConnection('taufiq')
            ->belongsTo(User::class, 'caretakerID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Animals (Shafiqah's database)
     * Animals that were rescued from this rescue operation
     * This is a logical relationship - no database-level foreign key
     */
    public function animals()
    {
        return $this->setConnection('shafiqah')
            ->hasMany(Animal::class, 'rescueID', 'id');
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_SCHEDULED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_SUCCESS,
            self::STATUS_FAILED,
        ];
    }
}
