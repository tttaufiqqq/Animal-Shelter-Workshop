<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait CreatesPaymentBill
{
    public function createBill(Booking $booking, $adoptionFee, $selectedAnimals)
    {
        try {
            $user = Auth::user();
            $animalNames = $selectedAnimals->pluck('name')->toArray();
            $animalCount = count($animalNames);

            if ($animalCount === 1) {
                $billName = 'Adopt ' . substr($animalNames[0], 0, 20);
                $billDescription = 'Adoption fee for ' . $animalNames[0] . ' (Booking #' . $booking->id . ')';
            } else {
                $billName = 'Adopt ' . $animalCount . ' Animals';
                $billDescription = 'Adoption fee for ' . implode(', ', array_slice($animalNames, 0, 3)) . ($animalCount > 3 ? ' and ' . ($animalCount - 3) . ' more' : '') . ' (Booking #' . $booking->id . ')';
            }

            $referenceNo = 'BOOKING-' . $booking->id . '-' . time();

            $option = [
                'userSecretKey' => config('toyyibpay.key'),
                'categoryCode' => config('toyyibpay.category'),
                'billName' => $billName,
                'billDescription' => $billDescription,
                'billPriceSetting' => 1,
                'billPayorInfo' => 1,
                'billAmount' => ($adoptionFee) * 100,
                'billReturnUrl' => route('toyyibpay-status'),
                'billCallbackUrl' => route('toyyibpay-callback'),
                'billExternalReferenceNo' => $referenceNo,
                'billTo' => $user->name,
                'billEmail' => $user->email,
                'billPhone' => $user->phoneNum ?? '0000000000',
                'billSplitPayment' => 0,
                'billPaymentChannel' => 0,
                'billChargeToCustomer' => 1,
                'billContentEmail' => 'Thank you for adopting from our shelter!',
            ];

            $url = 'https://dev.toyyibpay.com/index.php/api/createBill';
            $response = Http::withoutVerifying()->asForm()->post($url, $option);
            $data = $response->json();

            if (isset($data[0]['BillCode'])) {
                $billCode = $data[0]['BillCode'];
                session(['bill_code' => $billCode, 'reference_no' => $referenceNo]);
                return redirect('https://dev.toyyibpay.com/' . $billCode);
            }

            $booking->update(['status' => 'Confirmed']);
            return redirect()->route('booking:main')->withErrors(['error' => 'Failed to create payment. Please try again.']);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP Request Error creating bill: ' . $e->getMessage(), ['booking_id' => $booking->id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('booking:main')->withErrors(['error' => 'Payment gateway connection error. Please try again later.']);
        } catch (\Exception $e) {
            Log::error('Error creating bill: ' . $e->getMessage(), ['booking_id' => $booking->id, 'user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('booking:main')->withErrors(['error' => 'Failed to create payment: ' . $e->getMessage()]);
        }
    }
}
