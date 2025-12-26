<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

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
     * Append custom attributes to the model's array form
     */
    protected $appends = ['url'];

    /**
     * Get the full URL for the image (Cloudinary URL)
     * This automatically generates the correct URL whether using local or cloud storage
     */
    public function getUrlAttribute()
    {
        try {
            // Generate Cloudinary URL using the image() method
            return cloudinary()->image($this->image_path)->toUrl();
        } catch (\Exception $e) {
            // Handle any errors (missing images, network issues, etc.)
            \Log::warning("Failed to generate Cloudinary URL for: {$this->image_path} (ID: {$this->id}) - " . $e->getMessage());
            return 'https://via.placeholder.com/400x300?text=Image+Not+Found';
        }
    }

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
