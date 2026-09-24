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
            'rentals' => Reservation::with(['customer', 'items.gown', 'gownRelease'])
                ->whereIn('status', ['confirmed', 'ready_for_pickup', 'released', 'overdue'])->orderBy('pickup_date')->get(),
            'cleanings' => CleaningRecord::with('gown')->where('status', 'pending')->latest()->get(),
            'base' => request()->user()->role,
        ]);
    }

    public function releaseGown(Request $request, Reservation $reservation)
    {
        abort_unless(in_array($reservation->status, ['confirmed', 'ready_for_pickup'], true), 422, 'Only confirmed reservations can be released.');
        abort_if($reservation->pickup_date->isFuture(), 422, 'This rental cannot be released before its pickup date.');
        $data = $request->validate(['condition_before' => ['required', Rule::in(['excellent', 'good', 'fair', 'damaged'])], 'notes' => ['nullable', 'string', 'max:1000']]);
        DB::transaction(function () use ($request, $reservation, $data) {
            GownRelease::updateOrCreate(['reservation_id' => $reservation->id], $data + [
                'processed_by' => $request->user()->id, 'release_date' => today(), 'release_time' => now()->format('H:i:s'),
            ]);
            $reservation->update(['status' => 'released']);
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
            $lateFee = (float) SystemSetting::where('setting_key', 'late_fee_per_day')->value('setting_value');
            if ($lateDays > 0 && $lateFee > 0) {
                $lateCharge = $lateDays * $lateFee;
                Penalty::create(['reservation_id' => $reservation->id, 'gown_return_id' => $rentalReturn->id, 'penalty_type' => 'late_return', 'amount' => $lateCharge, 'status' => 'pending']);
                $extraCharges += $lateCharge;
            }
            $reservation->update(['status' => 'returned', 'grand_total' => (float) $reservation->grand_total + $extraCharges, 'balance' => (float) $reservation->balance + $extraCharges]);
        });
        return redirect()->route($request->user()->role . '.rentals')->with('success', 'Return inspection recorded. Any cleaning or charges are now listed on the account.');
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

    public function recordPayment(Request $request, Reservation $reservation)
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0', 'lte:' . (float) $reservation->balance], 'payment_method' => ['required', Rule::in(['cash', 'gcash'])], 'payment_type' => ['required', Rule::in(['downpayment', 'rental_balance', 'security_deposit', 'other'])], 'remarks' => ['nullable', 'string', 'max:500']]);
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
        return back()->with('success', 'Payment ' . $payment->payment_reference . ' recorded.');
    }

    public function employees()
    {
        return view('management.employees', ['employees' => Employee::with('user')->latest()->paginate(15)]);
    }

    public function createEmployee(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'password' => ['required', 'string', 'min:8'], 'contact_number' => ['nullable', 'string', 'max:40'], 'position' => ['required', 'string', 'max:100']]);
        DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role' => 'employee']);
            Employee::create(['user_id' => $user->id, 'employee_code' => 'EMP-' . strtoupper(Str::random(8)), 'full_name' => $data['name'], 'contact_number' => $data['contact_number'] ?? null, 'position' => $data['position'], 'status' => 'active']);
        });
        return back()->with('success', 'Employee account created. Share the login details securely.');
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

    public function settings()
    {
        $settings = SystemSetting::pluck('setting_value', 'setting_key');
        return view('management.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate(['shop_name' => ['required', 'string', 'max:120'], 'contact_email' => ['nullable', 'email', 'max:255'], 'contact_number' => ['nullable', 'string', 'max:40'], 'late_fee_per_day' => ['required', 'numeric', 'min:0', 'max:100000']]);
        foreach ($data as $key => $value) SystemSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => (string) $value]);
        return back()->with('success', 'Boutique settings saved.');
    }
}
