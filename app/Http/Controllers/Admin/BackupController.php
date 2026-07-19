<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\BackupDatabases;
use App\Http\Controllers\Controller;
use App\Services\Backup\BackupManifest;
use Illuminate\Support\Facades\Cache;

class BackupController extends Controller
{
    public function __construct(private BackupManifest $manifest)
    {
    }

    public function index()
    {
        $backupsRoot = storage_path('app/backups');
        $runs = array_reverse($this->manifest->all($backupsRoot), true);
        $latest = Cache::get(BackupDatabases::STATUS_CACHE_KEY) ?? (reset($runs) ?: null);

        return view('admin.backups.index', [
            'latest' => $latest,
            'runs' => $runs,
        ]);
    }
}
