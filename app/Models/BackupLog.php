<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'backup';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'backup_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'connection_name',
        'backup_type',
        'status',
        'file_path',
        'file_size',
        'error_message',
        'backup_started_at',
        'backup_completed_at',
        'duration_seconds',
        'admin_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'backup_started_at' => 'datetime',
            'backup_completed_at' => 'datetime',
            'file_size' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    /**
     * Get the admin who triggered the backup.
     */
    public function admin()
    {
        return $this->belongsTo(BackupAdmin::class, 'admin_id');
    }

    /**
     * Get the latest successful backup for a connection.
     */
    public static function getLatestSuccessfulBackup(string $connectionName)
    {
        return self::where('connection_name', $connectionName)
            ->where('status', 'success')
            ->orderBy('backup_completed_at', 'desc')
            ->first();
    }

    /**
     * Get all database connections with their last backup status.
     */
    public static function getAllDatabasesStatus(): array
    {
        $connections = ['eilya', 'atiqah', 'shafiqah', 'danish', 'taufiq'];
        $statuses = [];

        foreach ($connections as $connection) {
            $lastBackup = self::getLatestSuccessfulBackup($connection);
            $statuses[$connection] = [
                'last_backup' => $lastBackup?->backup_completed_at,
                'file_path' => $lastBackup?->file_path,
                'file_size' => $lastBackup?->file_size,
            ];
        }

        return $statuses;
    }
}
