<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rescue extends Model
{
      protected $connection = 'mysql_device_1';

    protected $fillable = ['status', 'remarks', 'reportID', 'caretakerID'];
    protected $table = 'rescue';
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

    const STATUS_SCHEDULED = 'Scheduled';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_SUCCESS = 'Success';
    const STATUS_FAILED = 'Failed';
}
