<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\{Gown, Customer, Payment, Reservation, SystemSetting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Customer::where('user_id', auth()->id())->first();
        $recent = $customer ? Reservation::with('items.gown')->where('customer_id', $customer->id)->latest()->first() : null;
        return view('customer.dashboard', ['featured' => Gown::with('category')->whereIn('status', ['available', 'reserved', 'rented'])->latest()->take(4)->get(), 'recent' => $recent]);
    }

    public function catalog()
    {
        $gowns = Gown::with('category')->where('status', '!=', 'retired')->latest()->get();
        return view('customer.catalog', compact('gowns'));
    }

    public function details(Gown $gown)
    {
        abort_if($gown->status === 'retired', 404);
        $gown->load('category', 'accessories');
        return view('customer.gown-details', compact('gown'));
    }

    public function reserve(Gown $gown)
    {
        abort_unless(in_array($gown->status, ['available', 'reserved', 'rented'], true), 404);
        $lateFeePerDay = (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value');
        return view('customer.reserve', compact('gown', 'lateFeePerDay'));
    }

    public function storeReservation(Request $request, Gown $gown)
    {
        $data = $request->validate([
            'contact_number' => ['required', 'string', 'max:40'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after_or_equal:pickup_date'],
            'event_type' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'bust' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'waist' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'hips' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'length' => ['nullable', 'numeric', 'min:0', 'max:400'],
            'government_id' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'agreement_accepted' => ['accepted'],
            'payment_amount' => ['required', 'numeric', 'gt:0'],
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        if (Carbon::parse($data['return_date'])->gt(Carbon::parse($data['pickup_date'])->addDays(3))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'return_date' => 'A gown may be rented for up to 3 days from its pickup date.',
            ]);
        }
        $lateFeePerDay = (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value');
        if ($lateFeePerDay <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['return_date' => 'The shop must configure a late fee per day before accepting reservations.']);
        }
        $user = $request->user();
        $idPhotoPath = $request->file('government_id')->store('government-ids', 'local');
        $paymentProofPath = $request->file('payment_proof')->store('payment-proofs');
        $measurements = collect(['Bust' => $data['bust'] ?? null, 'Waist' => $data['waist'] ?? null, 'Hips' => $data['hips'] ?? null, 'Length' => $data['length'] ?? null])
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value, $key) => $key . ': ' . $value . ' cm')->values()->join('; ');
        $customer = Customer::updateOrCreate(['user_id' => $user->id], [
                'customer_code' => Customer::where('user_id', $user->id)->value('customer_code') ?? 'CUS-' . strtoupper(Str::random(8)),
                'full_name' => $user->name, 'contact_number' => $data['contact_number'], 'email' => $user->email, 'status' => 'active',
            ]);

        $reservation = DB::transaction(function () use ($gown, $customer, $user, $data, $idPhotoPath, $paymentProofPath, $measurements, $lateFeePerDay) {
            $lockedGown = Gown::whereKey($gown->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($lockedGown->status, ['available', 'reserved', 'rented'], true), 422, 'This gown cannot be reserved at this time.');
            // Keep the gown unavailable through its return and the following
            // three cleaning days, including when it is returned late.
            $overlap = Reservation::with('gownReturn')
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->whereHas('items', fn ($items) => $items->where('gown_id', $lockedGown->id))
                ->get()
                ->contains(function ($existing) use ($data) {
                    $occupiedFrom = Carbon::parse($existing->pickup_date)->startOfDay();
                    $returnDate = $existing->gownReturn?->actual_return_date ?? $existing->return_date;
                    $occupiedThrough = Carbon::parse($returnDate)->startOfDay()->addDays(3);
                    $requestedFrom = Carbon::parse($data['pickup_date'])->startOfDay();
                    $requestedThrough = Carbon::parse($data['return_date'])->startOfDay();

                    return $occupiedFrom->lte($requestedThrough) && $occupiedThrough->gte($requestedFrom);
                });
            if ($overlap) {
                throw \Illuminate\Validation\ValidationException::withMessages(['pickup_date' => 'This gown is already reserved for some or all of those dates. Please choose another date range.']);
            }
            $total = (float) $lockedGown->rental_price;
            if ((float) $data['payment_amount'] > $total) {
                throw \Illuminate\Validation\ValidationException::withMessages(['payment_amount' => 'Payment cannot be greater than the rental fee.']);
            }
            $booking = Reservation::create([
                'reservation_code' => 'SB-' . now()->format('ymd') . '-' . strtoupper(Str::random(5)),
                'customer_id' => $customer->id, 'created_by' => $user->id,
                'pickup_date' => $data['pickup_date'], 'return_date' => $data['return_date'],
                'rental_total' => $total, 'security_deposit_total' => 0,
                'late_fee_per_day' => $lateFeePerDay,
                'grand_total' => $total, 'balance' => $total,
                'status' => 'awaiting_payment', 'customer_notes' => 'Event: ' . $data['event_type'] . (!empty($data['notes']) ? "\n" . $data['notes'] : ''),
                'measurements' => $measurements ?: null,
                'government_id_photo_path' => $idPhotoPath,
                'agreement_accepted_at' => now(),
                'collateral_status' => 'not_received',
            ]);
            $booking->items()->create(['gown_id' => $lockedGown->id, 'rental_price' => $lockedGown->rental_price, 'security_deposit' => 0, 'quantity' => 1]);
            Payment::create([
                'reservation_id' => $booking->id, 'customer_id' => $customer->id,
                'payment_reference' => 'SBP-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),
                'payment_type' => (float) $data['payment_amount'] < $total ? 'downpayment' : 'rental_balance',
                'payment_method' => 'gcash', 'amount' => $data['payment_amount'], 'proof_of_payment' => $paymentProofPath,
                'status' => 'pending', 'remarks' => (float) $data['payment_amount'] < $total ? 'Non-refundable down payment submitted for review.' : 'Full rental payment submitted for review.',
            ]);
            return $booking;
        });
        return redirect()->route('customer.reservations')->with('success', 'Reservation ' . $reservation->reservation_code . ' submitted with ID and payment proof. Staff will verify your payment.');
    }

    public function reservations()
    {
        $customer = Customer::where('user_id', auth()->id())->first();
        $reservations = $customer ? Reservation::with(['items.gown', 'payments', 'gownReturn'])->where('customer_id', $customer->id)->latest()->get() : collect();
        return view('customer.reservations', compact('reservations'));
    }

    public function cancelReservation(Request $request, Reservation $reservation)
    {
        $customer = Customer::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($reservation->customer_id === $customer->id, 404);

        DB::transaction(function () use ($reservation) {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status, ['pending', 'awaiting_payment'], true), 422, 'Only requests that staff have not confirmed can be cancelled here. Please contact the boutique for help with a confirmed booking.');
            $locked->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Reservation cancelled. If you have already paid, contact the boutique to arrange the next steps.');
    }

    public function submitPayment(Request $request, Reservation $reservation)
    {
        $customer = Customer::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($reservation->customer_id === $customer->id, 404);
        abort_if(in_array($reservation->status, ['cancelled', 'rejected', 'completed'], true), 422, 'Payments are closed for this reservation.');
        $pendingAmount = (float) Payment::where('reservation_id', $reservation->id)->where('status', 'pending')->sum('amount');
        $payableNow = max(0, (float) $reservation->balance - $pendingAmount);
        abort_if($payableNow <= 0, 422, 'Your outstanding balance is already covered by payments waiting for review.');
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'lte:' . $payableNow],
            'payment_type' => ['required', Rule::in(['downpayment', 'rental_balance', 'penalty'])],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $path = $request->file('proof')->store('payment-proofs');
        Payment::create([
            'reservation_id' => $reservation->id, 'customer_id' => $customer->id,
            'payment_reference' => 'SBP-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),
            'payment_type' => $data['payment_type'], 'payment_method' => 'gcash', 'amount' => $data['amount'],
            'proof_of_payment' => $path, 'status' => 'pending', 'remarks' => $data['payment_type'] === 'downpayment' ? 'Non-refundable GCash down payment submitted by customer.' : 'GCash proof submitted by customer.',
        ]);
        return back()->with('success', 'Payment proof submitted. The boutique will verify it shortly.');
    }
}
