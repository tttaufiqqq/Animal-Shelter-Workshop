<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    /**
     * The connection name for the model.
     * Always stored on taufiq (PostgreSQL) as it's always online.
     */
    protected $connection = 'taufiq';

    /**
     * The table associated with the model.
     */
    protected $table = 'audit_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'category',
        'action',
        'entity_type',
        'entity_id',
        'source_database',
        'performed_at',
        'ip_address',
        'user_agent',
        'request_url',
        'http_method',
        'old_values',
        'new_values',
        'metadata',
        'status',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'performed_at' => 'datetime',
        'old_values' => 'array', // Auto-handles JSONB
        'new_values' => 'array', // Auto-handles JSONB
        'metadata' => 'array', // Auto-handles JSONB
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to User model (same database - taufiq).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('performed_at', [$from, $to]);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by entity type and ID.
     */
    public function scopeEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
            ->where('entity_id', $entityId);
    }

    /**
     * Scope to search across multiple text fields.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('user_name', 'ILIKE', "%{$search}%")
                ->orWhere('user_email', 'ILIKE', "%{$search}%")
                ->orWhere('action', 'ILIKE', "%{$search}%")
                ->orWhere('entity_type', 'ILIKE', "%{$search}%")
                ->orWhere('ip_address', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Get correlation ID from metadata.
     */
    public function getCorrelationIdAttribute()
    {
        return $this->metadata['correlation_id'] ?? null;
    }

    /**
     * Get formatted performed_at timestamp.
     */
    public function getFormattedPerformedAtAttribute()
    {
        return $this->performed_at->format('d M Y, H:i:s');
    }

    /**
     * Get user-friendly action name.
     */
    public function getActionLabelAttribute()
    {
        return str_replace('_', ' ', ucwords($this->action, '_'));
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColorAttribute()
    {
        return match ($this->status) {
            'success' => 'green',
            'failure' => 'red',
            'error' => 'orange',
            default => 'gray',
        };
    }
}
