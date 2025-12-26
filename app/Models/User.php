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
        // Account management fields (added for admin user management)
        'account_status',
        'suspended_at',
        'suspended_by',
        'suspension_reason',
        'locked_until',
        'lock_reason',
        'failed_login_attempts',
        'last_failed_login_at',
        'require_password_reset',
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
            'suspended_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'require_password_reset' => 'boolean',
            'failed_login_attempts' => 'integer',
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

    /**
     * Relationship to UserAdminNotes (same database - taufiq)
     * Get all admin notes about this user
     */
    public function adminNotes()
    {
        return $this->hasMany(UserAdminNote::class, 'user_id', 'id')
            ->with('admin')
            ->latest();
    }

    /**
     * Relationship to UserAdminNotes (same database - taufiq)
     * Get notes created by this admin
     */
    public function createdNotes()
    {
        return $this->hasMany(UserAdminNote::class, 'admin_id', 'id')
            ->with('user')
            ->latest();
    }

    /**
     * Check if account is currently suspended
     */
    public function isSuspended(): bool
    {
        return $this->account_status === 'suspended';
    }

    /**
     * Check if account is currently locked
     */
    public function isLocked(): bool
    {
        if ($this->account_status === 'locked' && $this->locked_until) {
            // Check if lock has expired
            if (now()->greaterThan($this->locked_until)) {
                // Auto-unlock if expired
                $this->update([
                    'account_status' => 'active',
                    'locked_until' => null,
                    'lock_reason' => null,
                ]);
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Check if account can login (not suspended or locked)
     */
    public function canLogin(): bool
    {
        return !$this->isSuspended() && !$this->isLocked();
    }
}
