<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitList extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Danish's database)
    protected $connection = 'danish';

    protected $table = 'visit_list';

    protected $fillable = [
        'userID',
        'remarks'
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
     * CROSS-DATABASE: Many-to-many relationship to Animals (Shafiqah's database)
     * Through visit_list_animal pivot table
     */
    public function animals()
    {
        return $this->setConnection('shafiqah')
            ->belongsToMany(Animal::class, 'visit_list_animal', 'listID', 'animalID')
            ->withPivot('remarks')
            ->withTimestamps();
    }
}
