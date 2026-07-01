<?php

namespace App\Services\Concerns\Audit;

use App\Models\AuditLog;
use App\Models\Animal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait LogsDomainEvents
{
    public static function logAuthentication(
        string $action,
        ?string $email = null,
        ?string $error = null
    ): ?AuditLog {
        $request = request();
        $user = Auth::user();

        if ($action === 'login_failed' && $email && !$user) {
            $tempUser = (object) ['email' => $email];
            $ipAddress = self::getRealIpAddress($request, $tempUser);

            $userRecord = User::where('email', $email)->first();

            return AuditLog::create([
                'user_id' => $userRecord?->id,
                'user_name' => $userRecord?->name,
                'user_email' => $email,
                'user_role' => $userRecord ? $userRecord->getRoleNames()->first() : null,
                'category' => 'authentication',
                'action' => $action,
                'entity_type' => null,
                'entity_id' => null,
                'source_database' => 'users',
                'performed_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'request_url' => $request->fullUrl(),
                'http_method' => $request->method(),
                'old_values' => null,
                'new_values' => null,
                'metadata' => [
                    'email_attempted' => $email,
                    'mapped_ip' => $ipAddress,
                ],
                'status' => 'failure',
                'error_message' => $error,
            ]);
        }

        return self::log('authentication', $action, [
            'source_database' => 'users',
            'metadata' => [
                'email_attempted' => $email,
            ],
            'error_message' => $error,
        ], $error ? 'failure' : 'success');
    }

    public static function logPayment(
        string $action,
        int $bookingId,
        float $amount,
        array $animalIds,
        ?string $billCode = null,
        string $status = 'success'
    ): ?AuditLog {
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
            'source_database' => 'booking',
            'metadata' => [
                'amount' => $amount,
                'animal_ids' => $animalIds,
                'animal_names' => $animalNames,
                'bill_code' => $billCode,
                'payment_gateway' => 'ToyyibPay',
            ],
        ], $status);
    }

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
            'source_database' => 'animals',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => array_merge(['animal_name' => $animalName], $metadata),
        ]);
    }

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
            'source_database' => 'reporting',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
        ]);
    }

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
            'source_database' => 'animals',
            'metadata' => array_merge([
                'animal_id' => $animalId,
                'animal_name' => $animalName,
            ], $metadata),
        ]);
    }

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
            'source_database' => 'animals',
            'metadata' => array_merge([
                'animal_id' => $animalId,
                'animal_name' => $animalName,
            ], $metadata),
        ]);
    }
}
