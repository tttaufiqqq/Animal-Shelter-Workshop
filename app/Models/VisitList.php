<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitList extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Danish's database)
    protected $connection = 'booking';

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
        return $this->setConnection('users')
            ->belongsTo(User::class, 'userID', 'id');
    }

    /**
     * CROSS-DATABASE: Many-to-many relationship to Animals (Shafiqah's database)
     * Through visit_list_animal pivot table (on danish database)
     * Uses custom pivot model to specify the correct database connection
     *
     * IMPORTANT: setConnection('booking') ensures the pivot table is queried
     * from the correct database (Danish's SQL Server), not from the Animal model's
     * database (Shafiqah's MySQL)
     */
    public function animals()
    {
        return $this->setConnection('booking')
            ->belongsToMany(Animal::class, 'visit_list_animal', 'listID', 'animalID')
            ->using(VisitListAnimal::class)
            ->withPivot('remarks')
            ->withTimestamps();
    }
}
