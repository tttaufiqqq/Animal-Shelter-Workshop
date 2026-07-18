<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait VerifiesPaymentGateway
{
    private function getBillTransactions($billCode)
    {
        try {
            $response = Http::withoutVerifying()->asForm()->post('https://dev.toyyibpay.com/index.php/api/getBillTransactions', [
                'billCode' => $billCode,
                'userSecretKey' => config('toyyibpay.key'),
            ]);

            if ($response->failed()) {
                Log::error('Failed to fetch bill transactions', ['bill_code' => $billCode, 'status' => $response->status(), 'body' => $response->body()]);
                return [];
            }

            return $response->json();

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP Request Error fetching bill transactions: ' . $e->getMessage(), ['bill_code' => $billCode, 'trace' => $e->getTraceAsString()]);
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching bill transactions: ' . $e->getMessage(), ['bill_code' => $billCode, 'trace' => $e->getTraceAsString()]);
            return [];
        }
    }

    /**
     * ToyyibPay's billpaymentStatus: '1' = success, '2' = pending, '3' = failed.
     * The gateway, not the client-supplied status_id/statusId, is the source of truth.
     */
    private function isGatewayConfirmed($billCode): bool
    {
        return collect($this->getBillTransactions($billCode))
            ->contains(fn ($tx) => ($tx['billpaymentStatus'] ?? null) == '1');
    }
}
