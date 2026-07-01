<?php

namespace App\Services\Concerns\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait LogsCore
{
    protected static function getRealIpAddress($request, $user = null): string
    {
        if ($request->header('X-Forwarded-For')) {
            $ips = explode(',', $request->header('X-Forwarded-For'));
            return trim($ips[0]);
        }

        if ($request->header('X-Real-IP')) {
            return $request->header('X-Real-IP');
        }

        $remoteAddr = $request->ip();

        if ($remoteAddr === '127.0.0.1' || $remoteAddr === '::1') {
            $serverIp = getServerIpAddress();

            if ($serverIp && $serverIp !== '127.0.0.1') {
                session(['user_real_ip' => $serverIp]);
                \Log::info("Captured real IP address: {$serverIp} for user: " . ($user->email ?? 'guest'));
                return $serverIp;
            }

            if (session()->has('user_real_ip') && session('user_real_ip') !== '127.0.0.1') {
                return session('user_real_ip');
            }

            \Log::warning("Could not determine real IP address, falling back to localhost. Server IP detection returned: " . ($serverIp ?? 'null'));
            return $remoteAddr;
        }

        return $remoteAddr;
    }

    public static function log(
        string $category,
        string $action,
        array $data = [],
        string $status = 'success'
    ): ?AuditLog {
        try {
            $user = Auth::user();
            $request = request();

            $correlationId = $request->attributes->get('correlation_id')
                ?? session('audit_correlation_id')
                ?? Str::uuid()->toString();

            $ipAddress = self::getRealIpAddress($request, $user);

            $auditData = [
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'user_role' => $user ? $user->getRoleNames()->first() : null,
                'category' => $category,
                'action' => $action,
                'entity_type' => $data['entity_type'] ?? null,
                'entity_id' => $data['entity_id'] ?? null,
                'source_database' => $data['source_database'] ?? null,
                'performed_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'request_url' => $request->fullUrl(),
                'http_method' => $request->method(),
                'old_values' => $data['old_values'] ?? null,
                'new_values' => $data['new_values'] ?? null,
                'metadata' => array_merge($data['metadata'] ?? [], [
                    'correlation_id' => $correlationId,
                ]),
                'status' => $status,
                'error_message' => $data['error_message'] ?? null,
            ];

            return AuditLog::create($auditData);
        } catch (\Exception $e) {
            \Log::error('Audit logging failed: '.$e->getMessage(), [
                'category' => $category,
                'action' => $action,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
