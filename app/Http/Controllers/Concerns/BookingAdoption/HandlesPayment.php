<?php

namespace App\Http\Controllers\Concerns\BookingAdoption;

use App\Models\Animal;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Adoption;
use App\Models\Slot;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait HandlesPayment
{
    public function paymentStatus(Request $request)
    {
        $statusId = $request->input('status_id');
        $billCode = $request->input('billcode');
        $orderId = $request->input('order_id');

        $bookingId = session('booking_id');
        $adoptionFee = session('adoption_fee');
        $animalIds = session('animal_ids', []);
        $animalNames = session('animal_names');
        $referenceNo = session('reference_no');
        $feeBreakdowns = session('fee_breakdowns', []);

        $paymentStatus = $this->getBillTransactions($billCode);

        if ($statusId == 1) {
            if ($bookingId) {
                $booking = Booking::find($bookingId);

                if ($booking) {
                    DB::connection('booking')->beginTransaction();
                    DB::connection('animals')->beginTransaction();

                    $atiqahOnline = $this->isDatabaseAvailable('shelter');
                    if ($atiqahOnline) {
                        DB::connection('shelter')->beginTransaction();
                    }

                    try {
                        $booking->update(['status' => 'Completed']);
                        AuditService::logPayment('payment_completed', $bookingId, $adoptionFee, $animalIds, $billCode, 'success');

                        $affectedSlotIds = [];

                        foreach ($animalIds as $animalId) {
                            $animal = Animal::find($animalId);
                            if ($animal) {
                                $oldStatus = $animal->adoption_status;
                                $oldSlotId = $animal->slotID;

                                Animal::where('id', $animalId)->update(['adoption_status' => 'Adopted', 'slotID' => null]);

                                if ($oldSlotId) $affectedSlotIds[] = $oldSlotId;

                                AuditService::logAnimal('adoption_status_changed', $animalId, $animal->name, ['adoption_status' => $oldStatus, 'slotID' => $oldSlotId], ['adoption_status' => 'Adopted', 'slotID' => null], ['booking_id' => $bookingId, 'adopter_id' => Auth::id(), 'adopter_name' => Auth::user()->name, 'adoption_fee' => $adoptionFee / count($animalIds)]);
                            }
                        }

                        if ($atiqahOnline && !empty($affectedSlotIds)) {
                            foreach (array_unique($affectedSlotIds) as $slotId) {
                                $slot = Slot::find($slotId);
                                if ($slot) {
                                    $remainingAnimals = Animal::where('slotID', $slotId)->count();
                                    $slot->status = $remainingAnimals >= $slot->capacity ? 'occupied' : 'available';
                                    $slot->save();
                                }
                            }
                        }

                        DB::connection('booking')->commit();
                        DB::connection('animals')->commit();
                        if ($atiqahOnline) DB::connection('shelter')->commit();

                    } catch (\Exception $e) {
                        DB::connection('booking')->rollBack();
                        DB::connection('animals')->rollBack();
                        if ($atiqahOnline) DB::connection('shelter')->rollBack();
                        throw $e;
                    }

                    $transaction = Transaction::create([
                        'amount' => $adoptionFee,
                        'status' => 'Success',
                        'remarks' => 'Adoption payment for ' . $animalNames . ' (Booking #' . $bookingId . ')',
                        'type' => 'FPX Online Banking',
                        'bill_code' => $billCode,
                        'reference_no' => $referenceNo,
                        'userID' => Auth::id(),
                    ]);

                    foreach ($animalIds as $index => $animalId) {
                        $animal = Animal::find($animalId);
                        $animalName = $animal ? $animal->name : 'Unknown';
                        $individualFee = isset($feeBreakdowns[$animalId]) ? $feeBreakdowns[$animalId] : ($adoptionFee / count($animalIds));

                        $adoption = Adoption::create(['fee' => $individualFee, 'remarks' => 'Adopted: ' . $animalName, 'bookingID' => $bookingId, 'transactionID' => $transaction->id, 'animalID' => $animalId]);

                        AuditService::log('payment', 'adoption_completed', [
                            'entity_type' => 'Adoption', 'entity_id' => $adoption->id, 'source_database' => 'booking',
                            'metadata' => ['animal_id' => $animalId, 'animal_name' => $animalName, 'adoption_fee' => $individualFee, 'booking_id' => $bookingId, 'transaction_id' => $transaction->id, 'adopter_id' => Auth::id(), 'adopter_name' => Auth::user()->name],
                        ]);
                    }

                    $user = Auth::user();
                    if ($user->hasRole('public user')) {
                        $user->removeRole('public user');
                        $user->assignRole('adopter');
                    } elseif ($user->hasRole('caretaker') && !$user->hasRole('adopter')) {
                        $user->assignRole('adopter');
                    }

                    session()->forget(['booking_id', 'adoption_fee', 'animal_ids', 'animal_names', 'bill_code', 'reference_no', 'fee_breakdowns']);
                }
            }
        } else {
            if ($bookingId) {
                $booking = Booking::find($bookingId);
                if ($booking && strtolower($booking->status) == 'confirmed') {
                    Transaction::create(['amount' => $adoptionFee, 'status' => 'Failed', 'remarks' => 'Failed adoption payment for ' . $animalNames . ' (Booking #' . $bookingId . ')', 'type' => 'Adoption Fee', 'bill_code' => $billCode, 'reference_no' => $referenceNo, 'userID' => Auth::id()]);
                    AuditService::logPayment('payment_failed', $bookingId, $adoptionFee, $animalIds, $billCode, 'failure');
                }
            }
        }

        session()->flash('payment_status', ['status_id' => $statusId, 'billcode' => $billCode, 'order_id' => $orderId, 'booking_id' => $bookingId, 'amount' => $adoptionFee, 'animal_names' => $animalNames, 'animal_count' => count($animalIds), 'reference_no' => $referenceNo, 'payment_details' => $paymentStatus]);

        return redirect()->route('booking:main')->with('show_payment_modal', true);
    }

    public function callback(Request $request)
    {
        try {
            Log::info('ToyyibPay Callback:', $request->all());

            $billCode = $request->input('billcode');
            $statusId = $request->input('status_id');
            $referenceNo = $request->input('refno');

            if (strpos($referenceNo, 'BOOKING-') !== false) {
                $parts = explode('-', $referenceNo);
                if (isset($parts[1])) {
                    $bookingId = $parts[1];
                    $booking = Booking::find($bookingId);

                    if ($booking && $statusId == 1) {
                        DB::connection('booking')->beginTransaction();
                        DB::connection('animals')->beginTransaction();

                        $atiqahOnline = $this->isDatabaseAvailable('shelter');
                        if ($atiqahOnline) DB::connection('shelter')->beginTransaction();

                        try {
                            $booking->update(['status' => 'Completed']);
                            $affectedSlotIds = [];
                            $animalIds = $booking->animals->pluck('id')->toArray();

                            foreach ($animalIds as $animalId) {
                                $animal = Animal::find($animalId);
                                if ($animal) {
                                    $oldSlotId = $animal->slotID;
                                    Animal::where('id', $animalId)->update(['adoption_status' => 'Adopted', 'slotID' => null]);
                                    if ($oldSlotId) $affectedSlotIds[] = $oldSlotId;
                                }
                            }

                            if ($atiqahOnline && !empty($affectedSlotIds)) {
                                foreach (array_unique($affectedSlotIds) as $slotId) {
                                    $slot = Slot::find($slotId);
                                    if ($slot) {
                                        $remainingAnimals = Animal::where('slotID', $slotId)->count();
                                        $slot->status = $remainingAnimals >= $slot->capacity ? 'occupied' : 'available';
                                        $slot->save();
                                    }
                                }
                            }

                            $existingTransaction = Transaction::where('bill_code', $billCode)->first();
                            if (!$existingTransaction) {
                                Transaction::create(['amount' => $request->input('amount') / 100, 'status' => 'Success', 'remarks' => 'Adoption payment (Callback) - Booking #' . $bookingId . ' (' . count($animalIds) . ' animals)', 'date' => now(), 'type' => 'Adoption Fee', 'bill_code' => $billCode, 'reference_no' => $referenceNo, 'userID' => $booking->userID]);
                            }

                            DB::connection('booking')->commit();
                            DB::connection('animals')->commit();
                            if ($atiqahOnline) DB::connection('shelter')->commit();

                        } catch (\Exception $e) {
                            DB::connection('booking')->rollBack();
                            DB::connection('animals')->rollBack();
                            if ($atiqahOnline) DB::connection('shelter')->rollBack();
                            throw $e;
                        }
                    }
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('ToyyibPay Callback Error: ' . $e->getMessage(), ['request_data' => $request->all(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Callback processing failed: ' . $e->getMessage()], 500);
        }
    }

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
}
