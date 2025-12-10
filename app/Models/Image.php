<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Eilya's database)
    protected $connection = 'eilya';

    protected $table = 'image';

    protected $fillable = [
        'image_path',
        'animalID',
        'reportID',
        'clinicID',
    ];

    /**
     * CROSS-DATABASE: Relationship to Animal model (Shafiqah's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function animal()
    {
        return $this->setConnection('shafiqah')
            ->belongsTo(Animal::class, 'animalID', 'id');
    }

    /**
     * Relationship to Report model (same database - eilya)
     */
    public function report()
    {
        return $this->belongsTo(Report::class, 'reportID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Clinic model (Shafiqah's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinicID', 'id');
    }
}
