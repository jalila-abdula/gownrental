<?php

namespace App\Http\Controllers;

use App\Models\{CleaningRecord, Customer, DamageReport, Employee, Gown, GownRelease, GownReturn, MaintenanceRecord, Payment, Penalty, Reservation, SystemSetting, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ManagementController extends Controller
{
    public function rentals()
    {
        return view('management.rentals', [
            'rentals' => Reservation::with(['customer', 'items.gown', 'gownRelease', 'gownReturn'])
                ->whereIn('status', ['confirmed', 'ready_for_pickup', 'released', 'overdue', 'returned', 'completed'])->orderBy('pickup_date')->get(),
            'cleanings' => CleaningRecord::with('gown')->where('status', 'pending')->latest()->get(),
            'pickupsToday' => Reservation::whereIn('status', ['confirmed', 'ready_for_pickup'])->whereDate('pickup_date', today())->count(),
            'activeRentals' => Reservation::whereIn('status', ['released', 'overdue'])->count(),
            'returnsDue' => Reservation::whereIn('status', ['released', 'overdue'])->whereDate('return_date', '<=', today())->count(),
            'cleaningCount' => CleaningRecord::where('status', 'pending')->count(),
            'base' => request()->user()->role,
        ]);
    }

    public function employeeReservationForm(Gown $gown)
    {
        abort_unless($gown->status === 'available', 422, 'Only gowns currently marked available can start a new reservation.');
        return view('management.reservation-create', [
            'customers' => Customer::where('status', 'active')->orderBy('full_name')->get(),
            'gown' => $gown,
            'lateFeePerDay' => (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value'),
        ]);
    }

    public function storeEmployeeReservation(Request $request)
    {
        $data = $request->validate([
            'existing_customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['required_without:existing_customer_id', 'nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'contact_number' => ['required_without:existing_customer_id', 'nullable', 'string', 'max:40'],
            'gown_id' => ['required', 'exists:gowns,id'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after_or_equal:pickup_date'],
            'bust' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'waist' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'hips' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'length' => ['nullable', 'numeric', 'min:0', 'max:400'],
            'government_id' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'id_safe_slot' => ['required', 'string', 'max:80'],
            'agreement_accepted' => ['accepted'],
            'payment_amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', Rule::in(['cash', 'card'])],
        ]);
        if (Carbon::parse($data['return_date'])->gt(Carbon::parse($data['pickup_date'])->addDays(3))) {
            throw ValidationException::withMessages(['return_date' => 'A gown may be rented for up to 3 days from its pickup date.']);
        }
        $lateFeePerDay = (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value');
        if ($lateFeePerDay <= 0) {
            throw ValidationException::withMessages(['return_date' => 'Set the shop late fee per day in settings before accepting reservations.']);
        }

        $customer = !empty($data['existing_customer_id'])
            ? Customer::findOrFail($data['existing_customer_id'])
            : Customer::create([
                'customer_code' => 'CUS-' . strtoupper(Str::random(8)),
                'full_name' => $data['customer_name'], 'contact_number' => $data['contact_number'],
                'email' => $data['customer_email'] ?? null, 'status' => 'active',
            ]);
        abort_unless($customer->status === 'active', 422, 'This customer account cannot make new reservations.');
        $idPath = $request->file('government_id')->store('government-ids', 'local');
        $measurements = collect(['Bust' => $data['bust'] ?? null, 'Waist' => $data['waist'] ?? null, 'Hips' => $data['hips'] ?? null, 'Length' => $data['length'] ?? null])
            ->filter(fn ($value) => $value !== null)->map(fn ($value, $key) => $key . ': ' . $value . ' cm')->values()->join('; ');

        $reservation = DB::transaction(function () use ($request, $data, $customer, $idPath, $measurements, $lateFeePerDay) {
            $gown = Gown::whereKey($data['gown_id'])->lockForUpdate()->firstOrFail();
            abort_unless($gown->status === 'available', 422, 'This gown is not currently available for new reservations.');
            $overlap = Reservation::with('gownReturn')->whereNotIn('status', ['cancelled', 'rejected'])
                ->whereHas('items', fn ($items) => $items->where('gown_id', $gown->id))->get()
                ->contains(function ($existing) use ($data) {
                    $end = $existing->gownReturn?->actual_return_date ?? $existing->return_date;
                    return Carbon::parse($existing->pickup_date)->startOfDay()->lte(Carbon::parse($data['return_date'])->startOfDay())
                        && Carbon::parse($end)->startOfDay()->addDays(3)->gte(Carbon::parse($data['pickup_date'])->startOfDay());
                });
            if ($overlap) throw ValidationException::withMessages(['gown_id' => 'This gown is already booked or in its cleaning period for those dates.']);

            $total = (float) $gown->rental_price;
            if ((float) $data['payment_amount'] > $total) throw ValidationException::withMessages(['payment_amount' => 'Payment cannot exceed the rental fee.']);
            $booking = Reservation::create([
                'reservation_code' => 'SB-' . now()->format('ymd') . '-' . strtoupper(Str::random(5)),
                'customer_id' => $customer->id, 'created_by' => $request->user()->id,
                'pickup_date' => $data['pickup_date'], 'return_date' => $data['return_date'],
                'rental_total' => $total, 'security_deposit_total' => 0, 'late_fee_per_day' => $lateFeePerDay, 'grand_total' => $total,
                'amount_paid' => $data['payment_amount'], 'balance' => $total - (float) $data['payment_amount'],
                'status' => 'confirmed', 'measurements' => $measurements ?: null,
                'government_id_photo_path' => $idPath, 'physical_id_photo_path' => $idPath,
                'id_safe_slot' => $data['id_safe_slot'], 'agreement_accepted_at' => now(), 'collateral_status' => 'held',
            ]);
            $booking->items()->create(['gown_id' => $gown->id, 'rental_price' => $total, 'security_deposit' => 0, 'quantity' => 1]);
            Payment::create([
                'reservation_id' => $booking->id, 'customer_id' => $customer->id,
                'payment_reference' => 'PAY-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),
                'payment_type' => (float) $data['payment_amount'] < $total ? 'downpayment' : 'rental_balance',
                'payment_method' => $data['payment_method'], 'amount' => $data['payment_amount'],
                'status' => 'verified', 'verified_by' => $request->user()->id, 'verified_at' => now(),
                'remarks' => (float) $data['payment_amount'] < $total ? 'Non-refundable in-store down payment.' : 'Full in-store rental payment.',
            ]);
            return $booking;
        });

        return redirect()->route($request->user()->role . '.reservations')->with('success', 'In-store reservation ' . $reservation->reservation_code . ' created and payment recorded.');
    }

    public function releaseGown(Request $request, Reservation $reservation)
    {
        abort_unless(in_array($reservation->status, ['confirmed', 'ready_for_pickup'], true), 422, 'Only confirmed reservations can be released.');
        abort_if($reservation->pickup_date->isFuture(), 422, 'This rental cannot be released before its pickup date.');
        $data = $request->validate([
            'condition_before' => ['required', Rule::in(['excellent', 'good', 'fair', 'damaged'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'physical_id_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'id_safe_slot' => ['required', 'string', 'max:80'],
        ]);
        if (!$reservation->physical_id_photo_path && !$request->hasFile('physical_id_photo')) {
            throw ValidationException::withMessages(['physical_id_photo' => 'Capture a photo of the physical ID before storing it.']);
        }
        $physicalIdPath = $request->file('physical_id_photo')?->store('government-ids', 'local') ?? $reservation->physical_id_photo_path;
        DB::transaction(function () use ($request, $reservation, $data, $physicalIdPath) {
            GownRelease::updateOrCreate(['reservation_id' => $reservation->id], [
                'condition_before' => $data['condition_before'], 'notes' => $data['notes'] ?? null,
                'processed_by' => $request->user()->id, 'release_date' => today(), 'release_time' => now()->format('H:i:s'),
            ]);
            $reservation->update(['status' => 'released', 'physical_id_photo_path' => $physicalIdPath, 'id_safe_slot' => $data['id_safe_slot'], 'collateral_status' => 'held']);
            foreach ($reservation->items()->with('gown')->get() as $item) $item->gown?->update(['status' => 'rented']);
        });
        return back()->with('success', 'Gown handoff recorded. Rental is now active.');
    }

    public function returnGown(Request $request, Reservation $reservation)
    {
        abort_unless(in_array($reservation->status, ['released', 'overdue'], true), 422, 'Only active rentals can be returned.');
        $data = $request->validate([
            'condition_after' => ['required', Rule::in(['excellent', 'good', 'fair', 'damaged'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'repair_cost' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
        ]);
        $lateDays = max(0, Carbon::parse($reservation->return_date)->startOfDay()->diffInDays(today(), false));
        DB::transaction(function () use ($request, $reservation, $data, $lateDays) {
            $rentalReturn = GownReturn::updateOrCreate(['reservation_id' => $reservation->id], [
                'processed_by' => $request->user()->id, 'actual_return_date' => today(), 'actual_return_time' => now()->format('H:i:s'),
                'condition_after' => $data['condition_after'], 'late_days' => $lateDays, 'notes' => $data['notes'] ?? null,
            ]);
            $extraCharges = 0;
            foreach ($reservation->items()->with('gown')->get() as $item) {
                $gown = $item->gown;
                if (!$gown) continue;
                if ($data['condition_after'] === 'damaged') {
                    $gown->update(['condition' => 'damaged', 'status' => 'damaged']);
                    $repairCost = (float) ($data['repair_cost'] ?? 0);
                    DamageReport::create(['gown_return_id' => $rentalReturn->id, 'gown_id' => $gown->id, 'damage_type' => 'Rental return damage', 'description' => $data['notes'] ?? 'Damage recorded during return inspection.', 'repair_cost' => $repairCost, 'severity' => 'moderate']);
                    if ($repairCost > 0) {
                        Penalty::create(['reservation_id' => $reservation->id, 'gown_return_id' => $rentalReturn->id, 'penalty_type' => 'damage_fee', 'amount' => $repairCost, 'status' => 'pending']);
                        $extraCharges += $repairCost;
                    }
                } else {
                    $gown->update(['status' => 'for_cleaning']);
                    CleaningRecord::create(['gown_id' => $gown->id, 'processed_by' => $request->user()->id, 'cleaning_date' => today(), 'cleaning_type' => 'Post-rental cleaning', 'status' => 'pending', 'notes' => 'Created after reservation ' . $reservation->reservation_code]);
                }
            }
            $lateFee = (float) $reservation->late_fee_per_day;
            if ($lateFee <= 0) $lateFee = (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value');
            if ($lateDays > 0 && $lateFee > 0) {
                $lateCharge = $lateDays * $lateFee;
                Penalty::create(['reservation_id' => $reservation->id, 'gown_return_id' => $rentalReturn->id, 'penalty_type' => 'late_return', 'amount' => $lateCharge, 'status' => 'pending']);
                $extraCharges += $lateCharge;
            }
            $reservation->update(['status' => 'returned', 'grand_total' => (float) $reservation->grand_total + $extraCharges, 'balance' => (float) $reservation->balance + $extraCharges]);
        });
        return redirect()->route($request->user()->role . '.rentals')->with('success', 'Return inspection recorded. Any cleaning or charges are now listed on the account.');
    }

    public function releaseIdCollateral(Request $request, Reservation $reservation)
    {
        abort_unless(in_array($reservation->status, ['returned', 'completed'], true), 422, 'The gown must be returned before its ID can be released.');
        abort_if((float) $reservation->balance > 0, 422, 'Clear all rental, damage, and late charges before releasing the ID.');
        abort_unless($reservation->collateral_status === 'held', 422, 'There is no held ID collateral to release.');
        $reservation->update(['collateral_status' => 'released']);
        return back()->with('success', 'Original ID collateral released to the customer.');
    }

    public function completeCleaning(Request $request, CleaningRecord $cleaning)
    {
        abort_unless($cleaning->status === 'pending', 422, 'This cleaning record is already closed.');
        $cleaning->update(['status' => 'completed', 'processed_by' => $request->user()->id, 'cleaning_date' => today()]);
        if ($cleaning->gown && $cleaning->gown->condition !== 'damaged' && $cleaning->gown->status === 'for_cleaning') {
            $cleaning->gown->update(['status' => 'available']);
        }
        return back()->with('success', 'Cleaning completed; the gown is available again.');
    }

    public function maintenance()
    {
        return view('management.maintenance', [
            'maintenanceRecords' => MaintenanceRecord::with(['gown', 'processedBy'])->latest()->paginate(15),
            'gowns' => Gown::whereNotIn('status', ['retired', 'rented'])->orderBy('name')->get(),
            'base' => request()->user()->role,
        ]);
    }

    public function createMaintenance(Request $request)
    {
        $data = $request->validate([
            'gown_id' => ['required', 'exists:gowns,id'],
            'maintenance_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'maintenance_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $gown = Gown::whereKey($data['gown_id'])->lockForUpdate()->firstOrFail();
            abort_if(in_array($gown->status, ['retired', 'rented'], true), 422, 'This gown cannot be sent to maintenance right now.');
            $activeRental = Reservation::whereIn('status', ['pending', 'awaiting_payment', 'confirmed', 'ready_for_pickup', 'released', 'overdue'])
                ->whereHas('items', fn ($items) => $items->where('gown_id', $gown->id))
                ->exists();
            abort_if($activeRental, 422, 'This gown is linked to an active reservation and cannot be sent to maintenance.');

            MaintenanceRecord::create($data + [
                'processed_by' => $request->user()->id,
                'cost' => $data['cost'] ?? 0,
                'status' => 'pending',
            ]);
            $gown->update(['status' => 'under_maintenance']);
        });

        return back()->with('success', 'Maintenance record added and gown removed from the available collection.');
    }

    public function completeMaintenance(Request $request, MaintenanceRecord $maintenance)
    {
        $data = $request->validate([
            'condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'damaged'])],
            'completion_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $maintenance, $data) {
            $record = MaintenanceRecord::whereKey($maintenance->id)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'pending', 422, 'This maintenance record is already completed.');
            $record->update([
                'status' => 'completed',
                'processed_by' => $request->user()->id,
                'notes' => trim(($record->notes ? $record->notes . "\n" : '') . ($data['completion_notes'] ?? '')) ?: null,
            ]);

            $gown = Gown::whereKey($record->gown_id)->lockForUpdate()->first();
            if ($gown) {
                $gown->condition = $data['condition'];
                $hasPendingMaintenance = MaintenanceRecord::where('gown_id', $gown->id)->where('status', 'pending')->exists();
                if (!$hasPendingMaintenance) {
                    $gown->status = $data['condition'] === 'damaged' ? 'damaged' : 'available';
                }
                $gown->save();
            }
        });

        return back()->with('success', 'Maintenance closed and the gown condition updated.');
    }

    public function reservations(Request $request)
    {
        $query = Reservation::with(['customer', 'items.gown'])->latest();
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('q')) $query->where(function ($q) use ($request) {
            $term = $request->string('q');
            $q->where('reservation_code', 'like', "%$term%")->orWhereHas('customer', fn ($c) => $c->where('full_name', 'like', "%$term%"));
        });
        return view('management.reservations', ['reservations' => $query->paginate(12)->withQueryString(), 'base' => $request->user()->role]);
    }

    public function updateReservation(Request $request, Reservation $reservation)
    {
        $allowedTransitions = match ($reservation->status) {
            'pending', 'awaiting_payment' => ['confirmed', 'cancelled', 'rejected'],
            'confirmed' => ['ready_for_pickup', 'cancelled', 'rejected'],
            'ready_for_pickup' => ['cancelled'],
            'returned' => ['completed'],
            default => [],
        };
        $data = $request->validate([
            'status' => ['required', Rule::in(array_merge([$reservation->status], $allowedTransitions))],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($data['status'] === 'completed') {
            abort_unless($reservation->status === 'returned', 422, 'Only returned rentals can be completed.');
            abort_if((float) $reservation->balance > 0, 422, 'Clear the outstanding balance before completing this rental.');
        }
        $reservation->update($data);
        return back()->with('success', 'Reservation status updated.');
    }

    public function customers()
    {
        $query = Customer::withCount('reservations')->with('reservations')->latest();
        if (request()->filled('q')) {
            $term = request('q');
            $query->where(fn ($builder) => $builder->where('full_name', 'like', "%$term%")
                ->orWhere('email', 'like', "%$term%")
                ->orWhere('contact_number', 'like', "%$term%"));
        }
        return view('management.customers', ['customers' => $query->paginate(15)->withQueryString()]);
    }

    public function customerDetails(Customer $customer)
    {
        $customer->load([
            'reservations' => fn ($query) => $query->latest(),
            'reservations.items.gown',
            'reservations.payments' => fn ($query) => $query->latest(),
            'reservations.penalties' => fn ($query) => $query->latest(),
            'reservations.gownReturn',
        ]);
        $lateFeePerDay = (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value');

        foreach ($customer->reservations as $reservation) {
            $actualLateDays = (int) ($reservation->gownReturn?->late_days ?? 0);
            $currentLateDays = 0;
            if (!$reservation->gownReturn && in_array($reservation->status, ['released', 'overdue'], true) && $reservation->return_date?->isBefore(today())) {
                $currentLateDays = Carbon::parse($reservation->return_date)->startOfDay()->diffInDays(today(), false);
            }
            $reservation->display_late_days = max($actualLateDays, $currentLateDays);
            $agreedLateFee = (float) $reservation->late_fee_per_day ?: $lateFeePerDay;
            $reservation->projected_late_fee = $currentLateDays * $agreedLateFee;
        }

        return view('management.customer-show', [
            'customer' => $customer,
            'lateFeePerDay' => $lateFeePerDay,
            'base' => request()->user()->role,
        ]);
    }

    public function payments()
    {
        return view('management.payments', ['payments' => Payment::with(['reservation', 'customer'])->latest()->paginate(15)]);
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['verified', 'rejected'])], 'remarks' => ['nullable', 'string', 'max:500']]);
        DB::transaction(function () use ($request, $payment, $data) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'pending', 422, 'This payment has already been reviewed.');
            if ($data['status'] === 'verified') {
                $reservation = Reservation::whereKey($locked->reservation_id)->lockForUpdate()->firstOrFail();
                abort_if(in_array($reservation->status, ['cancelled', 'rejected', 'completed'], true), 422, 'Payments cannot be approved for a closed reservation.');
                if ((float) $locked->amount > (float) $reservation->balance) {
                    throw ValidationException::withMessages(['payment' => 'This payment exceeds the reservation balance. Review the account before approving it.']);
                }
                $paid = (float) $reservation->amount_paid + (float) $locked->amount;
                $reservation->update(['amount_paid' => $paid, 'balance' => max(0, (float) $reservation->grand_total - $paid)]);
            }
            $locked->update([
                'status' => $data['status'], 'verified_by' => $data['status'] === 'verified' ? $request->user()->id : null,
                'verified_at' => $data['status'] === 'verified' ? now() : null,
                'remarks' => $data['remarks'] ?? $locked->remarks,
            ]);
        });
        return back()->with('success', 'Payment review saved.');
    }

    public function paymentProof(Request $request, Payment $payment)
    {
        if ($request->user()->role === 'customer') {
            abort_unless($payment->customer?->user_id === $request->user()->id, 404);
        } else {
            abort_unless(in_array($request->user()->role, ['owner', 'employee'], true), 403);
        }
        abort_unless($payment->proof_of_payment && Storage::disk('local')->exists($payment->proof_of_payment), 404);
        return response()->file(Storage::disk('local')->path($payment->proof_of_payment));
    }

    public function collateralPhoto(Request $request, Reservation $reservation, string $type)
    {
        abort_unless(in_array($request->user()->role, ['owner', 'employee'], true), 403);
        abort_unless(in_array($type, ['digital', 'physical'], true), 404);
        $path = $type === 'physical' ? $reservation->physical_id_photo_path : $reservation->government_id_photo_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        return response()->file(Storage::disk('local')->path($path));
    }

    public function recordPayment(Request $request, Reservation $reservation)
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0', 'lte:' . (float) $reservation->balance], 'payment_method' => ['required', Rule::in(['cash', 'gcash', 'card'])], 'payment_type' => ['required', Rule::in(['downpayment', 'rental_balance', 'other'])], 'remarks' => ['nullable', 'string', 'max:500']]);
        $reservation->loadMissing('customer');
        $payment = DB::transaction(function () use ($data, $reservation, $request) {
            $lockedReservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            abort_if(in_array($lockedReservation->status, ['cancelled', 'rejected', 'completed'], true), 422, 'Payments cannot be recorded for a closed reservation.');
            if ((float) $data['amount'] > (float) $lockedReservation->balance) {
                throw ValidationException::withMessages(['amount' => 'The payment is greater than the current outstanding balance. Refresh the page and try again.']);
            }
            $payment = Payment::create($data + [
                'reservation_id' => $lockedReservation->id, 'customer_id' => $lockedReservation->customer_id,
                'payment_reference' => 'PAY-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),
                'status' => 'verified', 'verified_by' => $request->user()->id, 'verified_at' => now(),
            ]);
            $amountPaid = (float) $lockedReservation->amount_paid + (float) $payment->amount;
            $lockedReservation->update(['amount_paid' => $amountPaid, 'balance' => max(0, (float) $lockedReservation->grand_total - $amountPaid)]);
            return $payment;
        });
        if ($payment->payment_type === 'downpayment') $payment->update(['remarks' => trim(($payment->remarks ? $payment->remarks . ' ' : '') . 'Non-refundable down payment.')]);
        return back()->with('success', 'Payment ' . $payment->payment_reference . ' recorded.');
    }

    public function employees()
    {
        return view('management.employees', ['employees' => Employee::with('user')->latest()->paginate(15)]);
    }

    public function createEmployeeForm()
    {
        return view('management.employee-create');
    }

    public function employeeDetails(Employee $employee)
    {
        $userId = $employee->user_id;
        $activities = collect()
            ->concat(GownRelease::with('reservation')->where('processed_by', $userId)->get()->map(fn ($row) => [
                'date' => $row->created_at, 'action' => 'Gown released',
                'description' => 'Reservation ' . ($row->reservation?->reservation_code ?? '—') . ' · ' . ucfirst($row->condition_before) . ' condition at handoff',
            ]))
            ->concat(GownReturn::with('reservation')->where('processed_by', $userId)->get()->map(fn ($row) => [
                'date' => $row->created_at, 'action' => 'Gown returned',
                'description' => 'Reservation ' . ($row->reservation?->reservation_code ?? '—') . ' · ' . $row->late_days . ' late day(s)',
            ]))
            ->concat(CleaningRecord::with('gown')->where('processed_by', $userId)->get()->map(fn ($row) => [
                'date' => $row->updated_at ?? $row->created_at, 'action' => 'Cleaning updated',
                'description' => ($row->gown?->name ?? 'Gown') . ' · ' . $row->cleaning_type . ' · ' . ucfirst($row->status),
            ]))
            ->concat(MaintenanceRecord::with('gown')->where('processed_by', $userId)->get()->map(fn ($row) => [
                'date' => $row->updated_at ?? $row->created_at, 'action' => 'Maintenance updated',
                'description' => ($row->gown?->name ?? 'Gown') . ' · ' . $row->maintenance_type . ' · ' . ucfirst($row->status),
            ]))
            ->concat(Payment::with('reservation')->where('verified_by', $userId)->get()->map(fn ($row) => [
                'date' => $row->verified_at ?? $row->updated_at, 'action' => 'Payment recorded or verified',
                'description' => $row->payment_reference . ' · Reservation ' . ($row->reservation?->reservation_code ?? '—') . ' · ₱' . number_format((float) $row->amount, 2),
            ]))
            ->sortByDesc('date')->values();

        return view('management.employee-show', compact('employee', 'activities'));
    }

    public function createEmployee(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'password' => ['required', 'string', 'min:8'], 'contact_number' => ['nullable', 'string', 'max:40'], 'position' => ['required', 'string', 'max:100']]);
        DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role' => 'employee']);
            Employee::create(['user_id' => $user->id, 'employee_code' => 'EMP-' . strtoupper(Str::random(8)), 'full_name' => $data['name'], 'contact_number' => $data['contact_number'] ?? null, 'position' => $data['position'], 'status' => 'active']);
        });
        return redirect()->route('owner.employees')->with('success', 'Employee account created. Share the login details securely.');
    }

    public function toggleEmployee(Employee $employee)
    {
        $employee->update(['status' => $employee->status === 'active' ? 'inactive' : 'active']);
        return back()->with('success', 'Employee access updated.');
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($employee->user_id)],
            'contact_number' => ['nullable', 'string', 'max:40'],
            'position' => ['required', 'string', 'max:100'],
        ]);
        DB::transaction(function () use ($data, $employee) {
            $employee->user()->update(['name' => $data['name'], 'email' => $data['email']]);
            $employee->update(['full_name' => $data['name'], 'contact_number' => $data['contact_number'] ?? null, 'position' => $data['position']]);
        });
        return back()->with('success', 'Employee information updated.');
    }

    public function reports()
    {
        return view('management.reports', [
            'reservationCount' => Reservation::count(), 'rentalCount' => Reservation::whereIn('status', ['released', 'overdue'])->count(),
            'paymentTotal' => Payment::where('status', 'verified')->sum('amount'), 'gownCount' => Gown::count(),
            'availableCount' => Gown::where('status', 'available')->count(),
            'popular' => Gown::withCount('reservationItems')->orderByDesc('reservation_items_count')->take(5)->get(),
            'monthly' => Reservation::whereYear('created_at', now()->year)->get(['created_at'])
                ->groupBy(fn ($reservation) => $reservation->created_at->month)
                ->map(fn ($rows) => $rows->count()),
        ]);
    }

    public function exportReports()
    {
        $year = now()->year;
        $reservationCount = Reservation::count();
        $rentalCount = Reservation::whereIn('status', ['released', 'overdue'])->count();
        $paymentTotal = Payment::where('status', 'verified')->sum('amount');
        $gownCount = Gown::count();
        $availableCount = Gown::where('status', 'available')->count();
        $monthly = Reservation::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as reservation_count')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('reservation_count', 'month');
        $popular = Gown::with('category')->withCount('reservationItems')
            ->orderByDesc('reservation_items_count')->take(5)->get();

        return response()->streamDownload(function () use (
            $year,
            $reservationCount,
            $rentalCount,
            $paymentTotal,
            $gownCount,
            $availableCount,
            $monthly,
            $popular
        ) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            $writeRow = static function (array $row) use ($stream): void {
                $row = array_map(static function ($value) {
                    $value = (string) $value;
                    return preg_match('/^[\x00-\x20]*[=+\-@]/', $value) ? "'" . $value : $value;
                }, $row);
                fputcsv($stream, $row);
            };

            $writeRow(['Business report', 'Shyra Beautique']);
            $writeRow(['Generated at', now()->format('Y-m-d H:i:s')]);
            $writeRow([]);
            $writeRow(['Summary', 'Metric', 'Value']);
            $writeRow(['Summary', 'Total reservations', $reservationCount]);
            $writeRow(['Summary', 'Active rentals', $rentalCount]);
            $writeRow(['Summary', 'Verified payments (PHP)', number_format((float) $paymentTotal, 2, '.', '')]);
            $writeRow(['Summary', 'Available gowns', $availableCount]);
            $writeRow(['Summary', 'Total gowns', $gownCount]);
            $writeRow([]);
            $writeRow(['Monthly reservations', 'Year', $year]);
            $writeRow(['Month', 'Reservations']);
            for ($month = 1; $month <= 12; $month++) {
                $writeRow([\Carbon\Carbon::create()->month($month)->format('F'), $monthly->get($month, 0)]);
            }
            $writeRow([]);
            $writeRow(['Most reserved gowns', 'Category', 'Bookings']);
            foreach ($popular as $gown) {
                $writeRow([$gown->name, $gown->category->name ?? 'Collection', $gown->reservation_items_count]);
            }

            fclose($stream);
        }, 'shyra-business-report-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function settings()
    {
        $settings = SystemSetting::pluck('setting_value', 'setting_key');
        return view('management.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate(['shop_name' => ['required', 'string', 'max:120'], 'contact_email' => ['nullable', 'email', 'max:255'], 'contact_number' => ['nullable', 'string', 'max:40'], 'late_fee_per_day' => ['required', 'numeric', 'min:0.01', 'max:100000']]);
        foreach ($data as $key => $value) SystemSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => (string) $value]);
        return back()->with('success', 'Boutique settings saved.');
    }
}
