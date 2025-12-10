<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    // Specify the database connection for this model (Taufiq's database)
    protected $connection = 'taufiq';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'city',
        'state',
        'address',
        'phoneNum',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship to AdopterProfile (same database - taufiq)
     */
    public function adopterProfile()
    {
        return $this->hasOne(AdopterProfile::class, 'adopterID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to VisitList (Danish's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function visitList()
    {
        return $this->setConnection('danish')
            ->hasOne(VisitList::class, 'userID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Reports (Eilya's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function reports()
    {
        return $this->setConnection('eilya')
            ->hasMany(Report::class, 'userID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Rescues (Eilya's database)
     * User as caretaker for rescue operations
     * This is a logical relationship - no database-level foreign key
     */
    public function rescues()
    {
        return $this->setConnection('eilya')
            ->hasMany(Rescue::class, 'caretakerID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Bookings (Danish's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function bookings()
    {
        return $this->setConnection('danish')
            ->hasMany(Booking::class, 'userID', 'id');
    }

    /**
     * CROSS-DATABASE: Relationship to Transactions (Danish's database)
     * This is a logical relationship - no database-level foreign key
     */
    public function transactions()
    {
        return $this->setConnection('danish')
            ->hasMany(Transaction::class, 'userID', 'id');
    }
}
