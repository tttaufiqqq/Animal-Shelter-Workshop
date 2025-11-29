<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitList extends Model
{
      protected $connection = 'sqlsrv_remote';

    protected $table = 'visit_list';

    protected $fillable = [
        'userID',
        'remarks',
    ];

    /**
     * The user who owns this visit list.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }

    /**
     * Animals inside the visit list.
     */
    public function animals()
    {
        return $this->belongsToMany(Animal::class, 'visit_list_animal', 'listID', 'animalID')
            ->withPivot('remarks')
            ->withTimestamps();
    }
}
