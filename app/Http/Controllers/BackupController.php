<?php

namespace App\Http\Controllers;

use App\Models\BackupAdmin;
use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * Databases to backup.
     */
    protected $connections = [
        'eilya' => ['driver' => 'mysql', 'port' => 3307],
        'atiqah' => ['driver' => 'mysql', 'port' => 3308],
        'shafiqah' => ['driver' => 'mysql', 'port' => 3309],
        'danish' => ['driver' => 'sqlsrv', 'port' => 1434],
        'taufiq' => ['driver' => 'pgsql', 'port' => 5434],
    ];

    /**
     * Show backup login page.
     */
    public function showLogin()
    {
        $unavailableDatabases = session('unavailable_databases', []);

        return view('backup.login', compact('unavailableDatabases'));
    }

    /**
     * Handle backup admin login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $admin = BackupAdmin::where('email', $credentials['email'])->first();

            if ($admin && Hash::check($credentials['password'], $admin->password)) {
                session(['backup_admin_id' => $admin->id]);
                session(['backup_admin_name' => $admin->name]);

                return redirect()->route('backup-dashboard')
                    ->with('success', 'Login successful!');
            }

            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->withInput();

        } catch (\Exception $e) {
            \Log::error('Backup login error: ' . $e->getMessage());
            return back()
                ->with('error', 'Backup database connection failed. Please check the backup database.')
                ->withInput();
        }
    }

    /**
     * Show backup dashboard.
     */
    public function showDashboard()
    {
        if (!session('backup_admin_id')) {
            return redirect()->route('backup-login')
                ->with('error', 'Please login to access the backup dashboard.');
        }

        try {
            // Check status of all databases
            $databaseStatus = $this->checkAllDatabasesStatus();

            // Get last backup information
            $backupLogs = BackupLog::getAllDatabasesStatus();

            // Get next scheduled backup (Sundays at 12:00 AM)
            $now = now();
            $nextBackup = $now->copy()->next(0)->setTime(0, 0); // 0 = Sunday

            // Recent backup logs
            $recentLogs = BackupLog::with('admin')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return view('backup.dashboard', compact(
                'databaseStatus',
                'backupLogs',
                'nextBackup',
                'recentLogs'
            ));

        } catch (\Exception $e) {
            \Log::error('Backup dashboard error: ' . $e->getMessage());
            return view('backup.dashboard')->with('error', 'Error loading dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Backup all databases.
     */
    public function backupAllDatabases()
    {
        if (!session('backup_admin_id')) {
            return redirect()->route('backup-login')
                ->with('error', 'Please login to perform backups.');
        }

        set_time_limit(600); // 10 minutes timeout

        $results = [];
        $allSuccess = true;

        foreach ($this->connections as $connection => $config) {
            try {
                $result = $this->backupDatabase($connection, $config, session('backup_admin_id'));
                $results[$connection] = $result;

                if ($result['status'] !== 'success') {
                    $allSuccess = false;
                }
            } catch (\Exception $e) {
                $results[$connection] = [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                $allSuccess = false;
            }
        }

        if ($allSuccess) {
            // Force logout after successful backup
            session()->forget(['backup_admin_id', 'backup_admin_name']);

            return redirect('/')
                ->with('success', 'All databases backed up successfully! You have been logged out.');
        } else {
            return redirect()->route('backup-dashboard')
                ->with('error', 'Some backups failed. Please check the logs.')
                ->with('backup_results', $results);
        }
    }

    /**
     * Backup a single database.
     */
    protected function backupDatabase(string $connection, array $config, $adminId)
    {
        $startTime = now();
        $driver = $config['driver'];
        $timestamp = now()->format('Y-m-d_His');
        $backupPath = storage_path("app/backups/{$connection}");

        // Create backup directory if it doesn't exist
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $backupFile = "{$backupPath}/{$timestamp}.sql";

        // Create backup log entry
        $log = BackupLog::create([
            'connection_name' => $connection,
            'backup_type' => 'manual',
            'status' => 'running',
            'backup_started_at' => $startTime,
            'admin_id' => $adminId,
        ]);

        try {
            // Get database credentials
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port");
            $database = config("database.connections.{$connection}.database");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");

            // Execute backup based on driver
            if ($driver === 'mysql') {
                $this->backupMySQL($host, $port, $database, $username, $password, $backupFile);
            } elseif ($driver === 'pgsql') {
                $this->backupPostgreSQL($host, $port, $database, $username, $password, $backupFile);
            } elseif ($driver === 'sqlsrv') {
                $this->backupSQLServer($host, $port, $database, $username, $password, $backupFile);
            }

            $endTime = now();
            $fileSize = file_exists($backupFile) ? filesize($backupFile) : 0;

            // Update log entry
            $log->update([
                'status' => 'success',
                'file_path' => $backupFile,
                'file_size' => $fileSize,
                'backup_completed_at' => $endTime,
                'duration_seconds' => $endTime->diffInSeconds($startTime),
            ]);

            return [
                'status' => 'success',
                'file' => $backupFile,
                'size' => $fileSize,
            ];

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'backup_completed_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Backup MySQL database.
     */
    protected function backupMySQL($host, $port, $database, $username, $password, $backupFile)
    {
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('MySQL backup failed: ' . implode("\n", $output));
        }
    }

    /**
     * Backup PostgreSQL database.
     */
    protected function backupPostgreSQL($host, $port, $database, $username, $password, $backupFile)
    {
        // Set PGPASSWORD environment variable
        putenv("PGPASSWORD={$password}");

        $command = sprintf(
            'pg_dump -h %s -p %s -U %s -d %s -F p -f %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );

        exec($command, $output, $returnCode);

        // Clear password
        putenv("PGPASSWORD");

        if ($returnCode !== 0) {
            throw new \Exception('PostgreSQL backup failed: ' . implode("\n", $output));
        }
    }

    /**
     * Backup SQL Server database.
     */
    protected function backupSQLServer($host, $port, $database, $username, $password, $backupFile)
    {
        // For SQL Server, use SQLCMD or invoke SQL query
        $command = sprintf(
            'sqlcmd -S %s,%s -U %s -P %s -Q "BACKUP DATABASE [%s] TO DISK = \'%s\' WITH FORMAT" 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            $database,
            $backupFile
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('SQL Server backup failed: ' . implode("\n", $output));
        }
    }

    /**
     * Check status of all databases.
     */
    protected function checkAllDatabasesStatus()
    {
        $status = [];

        foreach (array_keys($this->connections) as $connection) {
            try {
                $result = DB::connection($connection)->select('SELECT DATABASE() as db_name');
                $status[$connection] = [
                    'exists' => !empty($result) && !is_null($result[0]->db_name),
                    'connected' => true,
                ];
            } catch (\Exception $e) {
                $status[$connection] = [
                    'exists' => false,
                    'connected' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $status;
    }

    /**
     * Logout from backup system.
     */
    public function logout()
    {
        session()->forget(['backup_admin_id', 'backup_admin_name']);

        return redirect()->route('backup-login')
            ->with('success', 'Logged out successfully.');
    }
}
