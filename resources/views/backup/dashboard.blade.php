<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="min-h-screen py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Database Backup Dashboard</h1>
                    <p class="text-gray-600 mt-1">Logged in as: <strong>{{ session('backup_admin_name') }}</strong></p>
                </div>
                <form method="POST" action="{{ route('backup-logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Database Status Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            @foreach($databaseStatus as $connection => $status)
                <div class="bg-white shadow rounded-lg p-6 {{ $status['exists'] ? 'border-l-4 border-green-500' : 'border-l-4 border-red-500' }}">
                    <h3 class="text-lg font-semibold text-gray-900 capitalize">{{ $connection }}</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        @if($status['exists'] && $status['connected'])
                            <span class="text-green-600 font-semibold">✓ Connected</span>
                        @else
                            <span class="text-red-600 font-semibold">✗ Not Found</span>
                        @endif
                    </p>

                    @if(isset($backupLogs[$connection]['last_backup']))
                        <p class="text-xs text-gray-500 mt-2">
                            Last Backup:<br>
                            <strong>{{ $backupLogs[$connection]['last_backup']->format('M d, Y H:i') }}</strong>
                        </p>
                        <p class="text-xs text-gray-500">
                            Size: <strong>{{ number_format($backupLogs[$connection]['file_size'] / 1024 / 1024, 2) }} MB</strong>
                        </p>
                    @else
                        <p class="text-xs text-gray-400 mt-2">No backup found</p>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Backup Controls -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Backup Controls</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Manual Backup</h3>
                    <p class="text-sm text-gray-600 mb-4">Create immediate backup of all databases</p>
                    <form method="POST" action="{{ route('backup-all-databases') }}" onsubmit="return confirm('This will backup all 5 databases. Continue?');">
                        @csrf
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                            Backup All Databases Now
                        </button>
                    </form>
                </div>

                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Next Scheduled Backup</h3>
                    <p class="text-sm text-gray-600 mb-2">Weekly backups every Sunday at 12:00 AM</p>
                    <p class="text-lg font-bold text-blue-600">{{ $nextBackup->format('l, F d, Y \a\t g:i A') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $nextBackup->diffForHumans() }}</p>
                </div>
            </div>
        </div>

        <!-- Recent Backup Logs -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Backup Logs</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Database</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize">{{ $log->connection_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ $log->backup_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->status === 'success')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->file_size ? number_format($log->file_size / 1024 / 1024, 2) . ' MB' : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->duration_seconds ? $log->duration_seconds . 's' : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->format('M d, Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No backup logs found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
