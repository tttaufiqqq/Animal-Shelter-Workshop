<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitList extends Model
{
    protected $table = 'VisitList';

    protected $fillable = [
        'userID',
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
        return $this->belongsToMany(Animal::class, 'VisitListAnimal', 'listID', 'animalID')
            ->withPivot('remarks')
            ->withTimestamps();
    }
}
