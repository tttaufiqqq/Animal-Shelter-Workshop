<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'taufiq';

    protected $table = 'audit_logs';

    const UPDATED_AT = null; // Audit logs are immutable

    protected $fillable = [
        'user_id',
        'user_email',
        'user_name',
        'action_type',
        'auditable_type',
        'auditable_id',
        'auditable_connection',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'changed_fields',
        'metadata',
    ];

    protected $casts = [
        'changed_fields' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationship to User (cross-database, logical only)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accessor: Get formatted action type
    public function getActionLabelAttribute()
    {
        return match ($this->action_type) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            'failed_login' => 'Failed Login Attempt',
            default => ucfirst($this->action_type),
        };
    }

    // Accessor: Get model name from auditable_type
    public function getModelNameAttribute()
    {
        if (! $this->auditable_type) {
            return null;
        }

        $parts = explode('\\', $this->auditable_type);

        return end($parts);
    }

    // Query Scopes for filtering
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAction($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeForModel($query, $modelClass)
    {
        return $query->where('auditable_type', $modelClass);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
