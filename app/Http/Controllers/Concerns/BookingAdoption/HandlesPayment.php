<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\Booking;
use App\Models\Transaction;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HandlesPayment
{
    use VerifiesPaymentGateway, CompletesBookingPayment;

    public function paymentStatus(Request $request)
    {
        $statusId = $request->input('status_id');
        $billCode = $request->input('billcode');
        $orderId = $request->input('order_id');

        $bookingId = session('booking_id');
        $adoptionFee = (float) session('adoption_fee', 0);
        $animalIds = session('animal_ids', []);
        $animalNames = session('animal_names');
        $referenceNo = session('reference_no');
        $feeBreakdowns = session('fee_breakdowns', []);

        $gatewayTransactions = $this->getBillTransactions($billCode);
        $gatewayConfirmed = collect($gatewayTransactions)->contains(fn ($tx) => ($tx['billpaymentStatus'] ?? null) == '1');

        if ($statusId == 1 && $gatewayConfirmed && $bookingId && ! empty($animalIds)) {
            $booking = Booking::find($bookingId);

            if ($booking) {
                $transaction = $this->completeBookingPayment($booking, $animalIds, $billCode, $referenceNo, $adoptionFee, $feeBreakdowns, $animalNames, Auth::id());

                if ($transaction) {
                    $user = Auth::user();
                    if ($user->hasRole('public user')) {
                        $user->removeRole('public user');
                        $user->assignRole('adopter');
                    } elseif ($user->hasRole('caretaker') && ! $user->hasRole('adopter')) {
                        $user->assignRole('adopter');
                    }
                }

                session()->forget(['booking_id', 'adoption_fee', 'animal_ids', 'animal_names', 'bill_code', 'reference_no', 'fee_breakdowns']);
            }
        } elseif ($bookingId) {
            $booking = Booking::find($bookingId);
            if ($booking && strtolower($booking->status) == 'confirmed') {
                Transaction::create([
                    'amount' => $adoptionFee,
                    'status' => 'Failed',
                    'remarks' => 'Failed adoption payment for ' . $animalNames . ' (Booking #' . $bookingId . ')',
                    'type' => 'Adoption Fee',
                    'bill_code' => $billCode,
                    'reference_no' => $referenceNo,
                    'userID' => Auth::id(),
                ]);
                AuditService::logPayment('payment_failed', $bookingId, $adoptionFee, $animalIds, $billCode, 'failure');
            }
        }

        session()->flash('payment_status', ['status_id' => $statusId, 'billcode' => $billCode, 'order_id' => $orderId, 'booking_id' => $bookingId, 'amount' => $adoptionFee, 'animal_names' => $animalNames, 'animal_count' => count($animalIds), 'reference_no' => $referenceNo, 'payment_details' => $gatewayTransactions]);

        return redirect()->route('booking:main')->with('show_payment_modal', true);
    }

    public function callback(Request $request)
    {
        try {
            Log::info('ToyyibPay Callback:', $request->all());

            $billCode = $request->input('billcode');
            $statusId = $request->input('status_id');
            $referenceNo = (string) $request->input('refno');

            preg_match('/BOOKING-(\d+)/', $referenceNo, $matches);
            $bookingId = $matches[1] ?? null;

            if ($bookingId && $statusId == 1) {
                $booking = Booking::find($bookingId);

                if ($booking && $this->isGatewayConfirmed($billCode)) {
                    // Not $booking->animals — that relation joins across two physically
                    // separate DB servers in one query, which MySQL/MariaDB can't do.
                    $animalIds = DB::connection('booking')
                        ->table('animal_booking')
                        ->where('bookingID', $booking->id)
                        ->pluck('animalID')
                        ->toArray();
                    $amount = $request->input('amount') / 100;

                    $this->completeBookingPayment($booking, $animalIds, $billCode, $referenceNo, $amount, [], null, $booking->userID);
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('ToyyibPay Callback Error: ' . $e->getMessage(), ['request_data' => $request->all(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Callback processing failed: ' . $e->getMessage()], 500);
        }
    }
}
