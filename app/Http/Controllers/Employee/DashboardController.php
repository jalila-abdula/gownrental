<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\{Gown, Reservation};

class DashboardController extends Controller
{
    public function index()
    {
        return view('employee.dashboard', [
            'today' => Reservation::whereDate('pickup_date', today())->count(),
            'pending' => Reservation::where('status', 'pending')->count(),
            'rentals' => Reservation::whereIn('status', ['released', 'overdue'])->count(),
            'available' => Gown::where('status', 'available')->count(),
            'reservations' => Reservation::with(['customer', 'items.gown'])->latest()->take(6)->get(),
            'returns' => Reservation::with('customer')->whereIn('status', ['released', 'overdue'])->orderBy('return_date')->take(4)->get(),
        ]);
    }
}
