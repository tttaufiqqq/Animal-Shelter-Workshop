<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
  protected $connection = 'mysql_device_1';

    protected $table = 'report';
    protected $fillable = [
        'latitude', 'longitude', 'address', 'city', 'state',
        'report_status', 'description', 'userID'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'reportID');
    }

    public function rescue()
    {
        return $this->hasOne(Rescue::class, 'reportID');
    }


}

