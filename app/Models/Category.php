<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    // Specify the database connection for this model (Atiqah's database)
    protected $connection = 'atiqah';

    protected $table = 'category';

    protected $fillable = [
        'main',
        'sub',
    ];

    /**
     * Relationship to Inventory items (same database - atiqah)
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'categoryID', 'id');
    }
}
