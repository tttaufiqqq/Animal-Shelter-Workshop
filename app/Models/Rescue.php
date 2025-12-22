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
        'priority',
        'remarks',
        'reportID',
        'caretakerID',
    ];

    // Status constants
    const STATUS_SCHEDULED = 'Scheduled';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_SUCCESS = 'Success';
    const STATUS_FAILED = 'Failed';

    // Priority constants
    const PRIORITY_CRITICAL = 'critical';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_NORMAL = 'normal';

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

    /**
     * Get all available priorities
     */
    public static function getPriorities()
    {
        return [
            self::PRIORITY_CRITICAL,
            self::PRIORITY_HIGH,
            self::PRIORITY_NORMAL,
        ];
    }

    /**
     * Map report description to priority level
     * Based on dropdown values in stray-reporting/create.blade.php
     */
    public static function getPriorityFromDescription($description)
    {
        // URGENT - Critical Priority
        $criticalDescriptions = [
            'Injured animal - Critical condition',
            'Trapped animal - Immediate rescue needed',
            'Aggressive animal - Public safety risk',
        ];

        // HIGH PRIORITY
        $highDescriptions = [
            'Sick animal - Needs medical attention',
            'Mother with puppies/kittens - Family rescue',
            'Young animal (puppy/kitten) - Vulnerable',
            'Malnourished animal - Needs care',
        ];

        // Check for critical priority
        if (in_array($description, $criticalDescriptions)) {
            return self::PRIORITY_CRITICAL;
        }

        // Check for high priority
        if (in_array($description, $highDescriptions)) {
            return self::PRIORITY_HIGH;
        }

        // Default to normal priority
        return self::PRIORITY_NORMAL;
    }
}
