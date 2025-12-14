<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitListAnimal extends Pivot
{
    use HasFactory;

    // Specify the database connection for this pivot model (Danish's database)
    protected $connection = 'danish';

    protected $table = 'visit_list_animal';

    // Increment IDs for pivot table
    public $incrementing = true;

    protected $fillable = [
        'listID',
        'animalID',
        'remarks',
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
     * Relationship to VisitList model (same database - danish)
     */
    public function visitList()
    {
        return $this->belongsTo(VisitList::class, 'listID', 'id');
    }
}
