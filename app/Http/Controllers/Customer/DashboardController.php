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
    /** Days a gown stays unavailable after return for professional cleaning. */
    public const CLEANING_DAYS = 3;

    /** Longest rental window measured from the pickup date. */
    public const MAX_RENTAL_DAYS = 3;

    /** Bump when the rental terms change so acceptances stay auditable. */
    public const AGREEMENT_VERSION = 'v1.0';

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
        $gown->load('category');
        $lateFeePerDay = (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value');
        $securityDeposit = (float) ($gown->security_deposit ?: 0);

        return view('customer.reserve', [
            'gown' => $gown,
            'lateFeePerDay' => $lateFeePerDay,
            'securityDeposit' => $securityDeposit,
            'maxRentalDays' => self::MAX_RENTAL_DAYS,
            'cleaningDays' => self::CLEANING_DAYS,
            'agreementVersion' => self::AGREEMENT_VERSION,
        ]);
    }

    /**
     * Live availability check backing step 1, so the customer cannot continue
     * on dates the gown is already spoken for (cleaning buffer included).
     */
    public function checkAvailability(Request $request, Gown $gown)
    {
        abort_unless(in_array($gown->status, ['available', 'reserved', 'rented'], true), 404);

        $data = $request->validate([
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after_or_equal:pickup_date'],
        ]);

        $pickup = Carbon::parse($data['pickup_date'])->startOfDay();
        $return = Carbon::parse($data['return_date'])->startOfDay();
        $days = $pickup->diffInDays($return);
        $conflicts = $this->conflictsFor($gown, $pickup, $return);

        $reason = null;
        if ($days > self::MAX_RENTAL_DAYS) {
            $reason = 'A gown may be rented for up to ' . self::MAX_RENTAL_DAYS . ' days from its pickup date.';
        } elseif ($conflicts) {
            $reason = 'This gown is already booked ' . $conflicts[0]['from']->toFormattedDateString()
                . ' \u2013 ' . $conflicts[0]['through']->toFormattedDateString()
                . '. Please pick different dates.';
        }

        return response()->json([
            'available' => $reason === null,
            'reason' => $reason,
            'summary' => $reason === null ? 'Available for your dates \u00b7 Pickup ' . $pickup->toFormattedDateString()
                . ' \u00b7 Return ' . $return->toFormattedDateString() : null,
            'rental_days' => $days + 1,
            'cleaning_until' => $return->copy()->addDays(self::CLEANING_DAYS)->toFormattedDateString(),
            'conflicts' => array_map(fn($block) => [
                'from' => $block['from']->toDateString(),
                'through' => $block['through']->toDateString(),
                'status' => $block['status'],
            ], $conflicts),
        ]);
    }

    public function storeReservation(Request $request, Gown $gown)
    {
        $data = $request->validate([
            'contact_number' => ['required', 'string', 'max:40'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after_or_equal:pickup_date'],
            'event_date' => ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:return_date'],
            'event_type' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'bust' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'waist' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'hips' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:250'],
            'length' => ['nullable', 'numeric', 'min:0', 'max:400'],
            'government_id' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'agreement_accepted' => ['accepted'],
            'agreement_penalties' => ['accepted'],
            'agreement_deposit' => ['accepted'],
            'payment_method' => ['required', Rule::in(['cash', 'gcash', 'bank_transfer'])],
            'payment_reference_number' => ['nullable', 'string', 'max:80', 'required_if:payment_method,gcash'],
            'payment_amount' => ['required', 'numeric', 'gt:0'],
            'payment_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        if (Carbon::parse($data['return_date'])->gt(Carbon::parse($data['pickup_date'])->addDays(self::MAX_RENTAL_DAYS))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'return_date' => 'A gown may be rented for up to ' . self::MAX_RENTAL_DAYS . ' days from its pickup date.',
            ]);
        }
        $lateFeePerDay = (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value');
        if ($lateFeePerDay <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['return_date' => 'The shop must configure a late fee per day before accepting reservations.']);
        }
        // Cash is settled at the counter during pickup, so it needs no receipt.
        if ($data['payment_method'] !== 'cash' && !$request->hasFile('payment_proof')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'payment_proof' => 'Upload a payment receipt for ' . str_replace('_', ' ', $data['payment_method']) . ' payments.',
            ]);
        }
        $user = $request->user();
        $idPhotoPath = $request->file('government_id')->store('government-ids', 'local');
        $paymentProofPath = $request->file('payment_proof')?->store('payment-proofs');
        $measurements = collect([
            'Bust' => $data['bust'] ?? null,
            'Waist' => $data['waist'] ?? null,
            'Hips' => $data['hips'] ?? null,
            'Height' => $data['height'] ?? null,
            'Length' => $data['length'] ?? null,
        ])->filter(fn($value) => $value !== null)
            ->map(fn($value, $key) => $key . ': ' . $value . ' cm')->values()->join('; ');
        $customer = Customer::updateOrCreate(['user_id' => $user->id], [
            'customer_code' => Customer::where('user_id', $user->id)->value('customer_code') ?? 'CUS-' . strtoupper(Str::random(8)),
            'full_name' => $user->name,
            'contact_number' => $data['contact_number'],
            'email' => $user->email,
            'status' => 'active',
        ]);

        $reservation = DB::transaction(function () use ($request, $gown, $customer, $user, $data, $idPhotoPath, $paymentProofPath, $measurements, $lateFeePerDay) {
            $lockedGown = Gown::whereKey($gown->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($lockedGown->status, ['available', 'reserved', 'rented'], true), 422, 'This gown cannot be reserved at this time.');
            // Keep the gown unavailable through its return and the following
            // three cleaning days, including when it is returned late.
            $conflicts = $this->conflictsFor($lockedGown, Carbon::parse($data['pickup_date']), Carbon::parse($data['return_date']));
            if ($conflicts) {
                throw \Illuminate\Validation\ValidationException::withMessages(['pickup_date' => 'This gown is already reserved for some or all of those dates. Please choose another date range.']);
            }
            // The deposit is collateral, not rental income: it is tracked
            // separately so it can be refunded, partially deducted, or fully
            // deducted after the gown is returned and inspected.
            $rentalFee = (float) $lockedGown->rental_price;
            $deposit = (float) ($lockedGown->security_deposit ?: 0);
            $amountDueNow = $rentalFee + $deposit;
            if ((float) $data['payment_amount'] > $amountDueNow) {
                throw \Illuminate\Validation\ValidationException::withMessages(['payment_amount' => 'Payment cannot be greater than the rental fee plus the security deposit.']);
            }
            $booking = Reservation::create([
                'reservation_code' => 'GR-' . now()->format('Y') . '-' . strtoupper(Str::random(6)),
                'customer_id' => $customer->id,
                'created_by' => $user->id,
                'pickup_date' => $data['pickup_date'],
                'return_date' => $data['return_date'],
                'event_date' => $data['event_date'] ?? null,
                'rental_total' => $rentalFee,
                'security_deposit_total' => $deposit,
                'late_fee_per_day' => $lateFeePerDay,
                'grand_total' => $amountDueNow,
                'balance' => $amountDueNow,
                'status' => 'pending',
                'customer_notes' => 'Event: ' . $data['event_type'] . (!empty($data['notes']) ? "\n" . $data['notes'] : ''),
                'measurements' => $measurements ?: null,
                'government_id_photo_path' => $idPhotoPath,
                'agreement_version' => self::AGREEMENT_VERSION,
                'agreement_accepted_at' => now(),
                'agreement_accepted_ip' => $request->ip(),
                'payment_reference_number' => $data['payment_reference_number'] ?? null,
                'collateral_status' => 'not_received',
            ]);
            $booking->items()->create([
                'gown_id' => $lockedGown->id,
                'rental_price' => $lockedGown->rental_price,
                'security_deposit' => $deposit,
                'quantity' => 1,
            ]);
            if ($deposit > 0) {
                $booking->securityDeposit()->create([
                    'amount' => $deposit,
                    'deducted_amount' => 0,
                    'refund_amount' => 0,
                    'status' => 'held',
                    'remarks' => 'Held as collateral and released after the gown is returned and inspected.',
                ]);
            }
            $isCash = $data['payment_method'] === 'cash';
            $isFull = (float) $data['payment_amount'] >= $amountDueNow;
            Payment::create([
                'reservation_id' => $booking->id,
                'customer_id' => $customer->id,
                'payment_reference' => 'SBP-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),
                'payment_type' => $isFull ? 'rental_balance' : 'downpayment',
                'payment_method' => $data['payment_method'],
                'amount' => $data['payment_amount'],
                'proof_of_payment' => $paymentProofPath,
                'status' => $isCash ? 'verified' : 'pending',
                'remarks' => $isCash
                    ? 'Cash payment to be settled at the counter during pickup.'
                    : ($isFull ? 'Full rental and deposit payment submitted for review.' : 'Non-refundable down payment submitted for review.'),
            ]);
            return $booking;
        });
        return redirect()->route('customer.reservations.show', $reservation)->with('success', 'Reservation ' . $reservation->reservation_code . ' submitted. Staff will review your agreement and payment.');
    }

    public function reservations()
    {
        $customer = Customer::where('user_id', auth()->id())->first();
        $reservations = $customer ? Reservation::with(['items.gown', 'payments', 'gownReturn', 'gownRelease', 'securityDeposit', 'penalties'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->get() : collect();

        return view('customer.reservations', [
            'reservations' => $reservations,
            'lifecycles' => $reservations->mapWithKeys(fn($reservation) => [$reservation->id => $this->lifecycleFor($reservation)]),
        ]);
    }

    public function showReservation(Reservation $reservation)
    {
        $customer = Customer::where('user_id', auth()->id())->firstOrFail();
        abort_unless($reservation->customer_id === $customer->id, 404);
        $reservation->load(['items.gown', 'payments', 'gownReturn', 'gownRelease', 'securityDeposit', 'penalties']);

        return view('customer.reservation-show', [
            'reservation' => $reservation,
            'lifecycle' => $this->lifecycleFor($reservation),
        ]);
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
            'payment_type' => ['required', Rule::in(['downpayment', 'rental_balance', 'security_deposit', 'penalty', 'damage_fee'])],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $path = $request->file('proof')->store('payment-proofs');
        Payment::create([
            'reservation_id' => $reservation->id,
            'customer_id' => $customer->id,
            'payment_reference' => 'SBP-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),
            'payment_type' => $data['payment_type'],
            'payment_method' => 'gcash',
            'amount' => $data['amount'],
            'proof_of_payment' => $path,
            'status' => 'pending',
            'remarks' => $data['payment_type'] === 'downpayment' ? 'Non-refundable GCash down payment submitted by customer.' : 'GCash proof submitted by customer.',
        ]);
        return back()->with('success', 'Payment proof submitted. The boutique will verify it shortly.');
    }

    /**
     * Date blocks for a gown covering the return date plus the cleaning
     * turnaround, so a gown can never be double-booked.
     *
     * @return array<int, array{from: \Illuminate\Support\Carbon, through: \Illuminate\Support\Carbon, status: string}>
     */
    private function conflictsFor(Gown $gown, Carbon $pickup, Carbon $return): array
    {
        return Reservation::with('gownReturn')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->whereHas('items', fn($items) => $items->where('gown_id', $gown->id))
            ->get()
            ->filter(function ($existing) use ($pickup, $return) {
                $occupiedFrom = Carbon::parse($existing->pickup_date)->startOfDay();
                $returnDate = $existing->gownReturn?->actual_return_date ?? $existing->return_date;
                $occupiedThrough = Carbon::parse($returnDate)->startOfDay()->addDays(self::CLEANING_DAYS);

                return $occupiedFrom->lte($return) && $occupiedThrough->gte($pickup);
            })
            ->map(fn($existing) => [
                'from' => Carbon::parse($existing->pickup_date)->startOfDay(),
                'through' => Carbon::parse($existing->gownReturn?->actual_return_date ?? $existing->return_date)->startOfDay()->addDays(self::CLEANING_DAYS),
                'status' => $existing->status,
            ])
            ->values()
            ->all();
    }

    /**
     * Builds the customer-visible lifecycle of a reservation so a booking can
     * show real progress instead of a single status string.
     *
     * @return array<int, array{key: string, label: string, state: string, meta: ?string}>
     */
    private function lifecycleFor(Reservation $reservation): array
    {
        $release = $reservation->gownRelease;
        $returned = $reservation->gownReturn;
        $deposit = $reservation->securityDeposit;
        $paid = (float) $reservation->amount_paid;
        $pending = (float) $reservation->payments->where('status', 'pending')->sum('amount');
        $closed = in_array($reservation->status, ['cancelled', 'rejected'], true);

        return [
            [
                'key' => 'requested',
                'label' => 'Reservation submitted',
                'state' => 'done',
                'meta' => $reservation->created_at?->toFormattedDateString(),
            ],
            [
                'key' => 'agreement',
                'label' => 'Rental agreement',
                'state' => $reservation->agreement_accepted_at ? 'done' : 'upcoming',
                'meta' => $reservation->agreement_accepted_at
                    ? 'Accepted · ' . $reservation->agreement_version . ' · ' . $reservation->agreement_accepted_at->toFormattedDateString()
                    : 'Awaiting acceptance',
            ],
            [
                'key' => 'payment',
                'label' => 'Payment',
                'state' => $closed ? 'upcoming' : ($pending > 0 ? 'current' : ($paid > 0 ? 'done' : 'upcoming')),
                'meta' => $pending > 0
                    ? '₱' . number_format($pending, 2) . ' under review'
                    : '₱' . number_format($paid, 2) . ' paid of ₱' . number_format($reservation->grand_total, 2),
            ],
            [
                'key' => 'pickup',
                'label' => 'Pickup',
                'state' => $release ? 'done' : 'upcoming',
                'meta' => $release?->release_date
                    ? 'Collected ' . $release->release_date->toFormattedDateString()
                    : 'Scheduled ' . ($reservation->pickup_date?->toFormattedDateString() ?? '—'),
            ],
            [
                'key' => 'return',
                'label' => 'Return',
                'state' => $returned?->actual_return_date ? 'done' : ($release ? 'current' : 'upcoming'),
                'meta' => $returned?->actual_return_date
                    ? 'Returned ' . $returned->actual_return_date->toFormattedDateString()
                    : 'Due ' . ($reservation->return_date?->toFormattedDateString() ?? '—'),
            ],
            [
                'key' => 'inspection',
                'label' => 'Inspection',
                'state' => !$returned ? 'upcoming' : ($reservation->status === 'completed' ? 'done' : 'current'),
                'meta' => $returned
                    ? ($returned->condition_after ? 'Condition: ' . $returned->condition_after : 'Awaiting inspection')
                    : 'Happens after return',
            ],
            [
                'key' => 'deposit',
                'label' => 'Security deposit',
                'state' => !$deposit ? 'upcoming' : ($returned ? 'current' : 'upcoming'),
                'meta' => $deposit
                    ? '₱' . number_format($deposit->amount, 2) . ' · ' . ucfirst(str_replace('_', ' ', $deposit->status))
                    : 'No deposit for this gown',
            ],
            [
                'key' => 'completed',
                'label' => 'Completed',
                'state' => $reservation->status === 'completed' ? 'done' : 'upcoming',
                'meta' => $reservation->status === 'completed' ? 'All settled' : 'Ends once the deposit is released',
            ],
        ];
    }
}
