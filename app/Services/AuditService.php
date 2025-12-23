<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Animal;
use App\Models\Vet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditService
{
    /**
     * Team member IP address mapping (from SSH tunnels setup)
     */
    protected static $teamIpMapping = [
        'eilya' => '10.18.26.14',
        'atiqah' => '10.18.26.84',
        'piqa' => '10.18.26.121',
        'shafiqah' => '10.18.26.121', // Same as piqa
        'danish' => '10.18.26.18',
        'taufiq' => '10.18.26.156',
    ];

    /**
     * Get the real IP address of the user/machine.
     * Captures the actual machine's IPv4 address instead of loopback (127.0.0.1).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User|null $user
     * @return string
     */
    protected static function getRealIpAddress($request, $user = null): string
    {
        // Check for X-Forwarded-For header (if using proxy/load balancer)
        if ($request->header('X-Forwarded-For')) {
            $ips = explode(',', $request->header('X-Forwarded-For'));
            return trim($ips[0]);
        }

        // Check for X-Real-IP header
        if ($request->header('X-Real-IP')) {
            return $request->header('X-Real-IP');
        }

        // If user is logged in, try to map to team member IP (for SSH tunnel scenario)
        if ($user && $user->email) {
            $emailPrefix = strtolower(explode('@', $user->email)[0]);

            // Check if email matches any team member
            foreach (self::$teamIpMapping as $teamMember => $ip) {
                if (str_contains($emailPrefix, $teamMember)) {
                    return $ip;
                }
            }
        }

        // Check REMOTE_ADDR (standard IP detection)
        $remoteAddr = $request->ip();

        // If it's localhost, get the actual machine's network IP address
        if ($remoteAddr === '127.0.0.1' || $remoteAddr === '::1') {
            // Try to get IP from session if previously cached
            if (session()->has('user_real_ip')) {
                return session('user_real_ip');
            }

            // Get the actual machine's IPv4 address (works on Windows/Linux/macOS)
            $serverIp = getServerIpAddress();
            if ($serverIp) {
                // Cache in session for performance
                session(['user_real_ip' => $serverIp]);
                return $serverIp;
            }

            // Fallback to localhost if we can't determine
            return $remoteAddr;
        }

        return $remoteAddr;
    }

    /**
     * Log an audit trail entry.
     *
     * @param string $category - 'authentication', 'payment', 'animal', 'rescue'
     * @param string $action - Specific action verb (e.g., 'login_success', 'payment_completed')
     * @param array $data - Context data (entity_id, old_values, new_values, metadata)
     * @param string $status - 'success', 'failure', 'error'
     * @return \App\Models\AuditLog|null
     */
    public static function log(
        string $category,
        string $action,
        array $data = [],
        string $status = 'success'
    ): ?AuditLog {
        try {
            $user = Auth::user();
            $request = request();

            // Get correlation ID from request, session, or generate new one
            $correlationId = $request->attributes->get('correlation_id')
                ?? session('audit_correlation_id')
                ?? Str::uuid()->toString();

            // Get real IP address (handles SSH tunnel scenario)
            $ipAddress = self::getRealIpAddress($request, $user);

            // Build audit record
            $auditData = [
                // User context
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'user_role' => $user ? $user->getRoleNames()->first() : null,

                // Action context
                'category' => $category,
                'action' => $action,
                'entity_type' => $data['entity_type'] ?? null,
                'entity_id' => $data['entity_id'] ?? null,
                'source_database' => $data['source_database'] ?? null,

                // Request context
                'performed_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'request_url' => $request->fullUrl(),
                'http_method' => $request->method(),

                // Data context - merge correlation_id into metadata
                'old_values' => $data['old_values'] ?? null,
                'new_values' => $data['new_values'] ?? null,
                'metadata' => array_merge($data['metadata'] ?? [], [
                    'correlation_id' => $correlationId,
                ]),

                // Outcome
                'status' => $status,
                'error_message' => $data['error_message'] ?? null,
            ];

            return AuditLog::create($auditData);
        } catch (\Exception $e) {
            // CRITICAL: Audit logging should NEVER break the application
            \Log::error('Audit logging failed: '.$e->getMessage(), [
                'category' => $category,
                'action' => $action,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Helper: Log authentication attempts.
     *
     * @param string $action - 'login_success', 'login_failed', 'logout'
     * @param string|null $email - Email attempted (for failed logins)
     * @param string|null $error - Error message (for failed logins)
     * @return \App\Models\AuditLog|null
     */
    public static function logAuthentication(
        string $action,
        ?string $email = null,
        ?string $error = null
    ): ?AuditLog {
        // For failed logins, we need to map IP based on email since user isn't authenticated
        // Temporarily create a user-like object for IP mapping
        $request = request();
        $user = Auth::user();

        // If login failed and we have an email, create temporary object for IP mapping
        if ($action === 'login_failed' && $email && !$user) {
            $tempUser = (object) ['email' => $email];
            $ipAddress = self::getRealIpAddress($request, $tempUser);

            // Manually build the audit data to override IP
            return self::log('authentication', $action, [
                'source_database' => 'taufiq',
                'metadata' => [
                    'email_attempted' => $email,
                    'mapped_ip' => $ipAddress,
                ],
                'error_message' => $error,
                // This will be overridden by getRealIpAddress in the main log method
            ], 'failure');
        }

        return self::log('authentication', $action, [
            'source_database' => 'taufiq',
            'metadata' => [
                'email_attempted' => $email,
            ],
            'error_message' => $error,
        ], $error ? 'failure' : 'success');
    }

    /**
     * Helper: Log payment operations.
     *
     * @param string $action - 'payment_initiated', 'payment_completed', 'payment_failed', etc.
     * @param int $bookingId - Booking ID
     * @param float $amount - Payment amount
     * @param array $animalIds - Array of animal IDs
     * @param string|null $billCode - ToyyibPay bill code
     * @param string $status - 'success', 'failure', 'error'
     * @return \App\Models\AuditLog|null
     */
    public static function logPayment(
        string $action,
        int $bookingId,
        float $amount,
        array $animalIds,
        ?string $billCode = null,
        string $status = 'success'
    ): ?AuditLog {
        // Get animal names for display
        $animalNames = [];
        foreach ($animalIds as $animalId) {
            $animal = Animal::find($animalId);
            if ($animal) {
                $animalNames[] = $animal->name;
            }
        }

        return self::log('payment', $action, [
            'entity_type' => 'Booking',
            'entity_id' => $bookingId,
            'source_database' => 'danish',
            'metadata' => [
                'amount' => $amount,
                'animal_ids' => $animalIds,
                'animal_names' => $animalNames,
                'bill_code' => $billCode,
                'payment_gateway' => 'ToyyibPay',
            ],
        ], $status);
    }

    /**
     * Helper: Log animal operations.
     *
     * @param string $action - 'animal_created', 'adoption_status_changed', 'medical_added', etc.
     * @param int $animalId - Animal ID
     * @param string $animalName - Animal name
     * @param array|null $oldValues - Old values (for updates)
     * @param array|null $newValues - New values (for updates)
     * @param array $metadata - Additional context
     * @return \App\Models\AuditLog|null
     */
    public static function logAnimal(
        string $action,
        int $animalId,
        string $animalName,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $metadata = []
    ): ?AuditLog {
        return self::log('animal', $action, [
            'entity_type' => 'Animal',
            'entity_id' => $animalId,
            'source_database' => 'shafiqah',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => array_merge(['animal_name' => $animalName], $metadata),
        ]);
    }

    /**
     * Helper: Log rescue operations.
     *
     * @param string $action - 'caretaker_assigned', 'status_updated', 'priority_changed', etc.
     * @param int $rescueId - Rescue ID
     * @param array|null $oldValues - Old values (for updates)
     * @param array|null $newValues - New values (for updates)
     * @param array $metadata - Additional context
     * @return \App\Models\AuditLog|null
     */
    public static function logRescue(
        string $action,
        int $rescueId,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $metadata = []
    ): ?AuditLog {
        return self::log('rescue', $action, [
            'entity_type' => 'Rescue',
            'entity_id' => $rescueId,
            'source_database' => 'eilya',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Helper: Log medical record operations.
     *
     * @param string $action - 'medical_added', 'medical_updated', 'medical_deleted'
     * @param int $animalId - Animal ID
     * @param string $animalName - Animal name
     * @param int $medicalId - Medical record ID
     * @param array $metadata - Medical record details
     * @return \App\Models\AuditLog|null
     */
    public static function logMedical(
        string $action,
        int $animalId,
        string $animalName,
        int $medicalId,
        array $metadata = []
    ): ?AuditLog {
        return self::log('animal', $action, [
            'entity_type' => 'Medical',
            'entity_id' => $medicalId,
            'source_database' => 'shafiqah',
            'metadata' => array_merge([
                'animal_id' => $animalId,
                'animal_name' => $animalName,
            ], $metadata),
        ]);
    }

    /**
     * Helper: Log vaccination record operations.
     *
     * @param string $action - 'vaccination_added', 'vaccination_updated', 'vaccination_deleted'
     * @param int $animalId - Animal ID
     * @param string $animalName - Animal name
     * @param int $vaccinationId - Vaccination record ID
     * @param array $metadata - Vaccination record details
     * @return \App\Models\AuditLog|null
     */
    public static function logVaccination(
        string $action,
        int $animalId,
        string $animalName,
        int $vaccinationId,
        array $metadata = []
    ): ?AuditLog {
        return self::log('animal', $action, [
            'entity_type' => 'Vaccination',
            'entity_id' => $vaccinationId,
            'source_database' => 'shafiqah',
            'metadata' => array_merge([
                'animal_id' => $animalId,
                'animal_name' => $animalName,
            ], $metadata),
        ]);
    }
}
