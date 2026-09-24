<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\{Gown, Reservation, Customer, Payment};

class DashboardController extends Controller
{
    public function index()
    {
        return view('owner.dashboard', [
            'gowns' => Gown::count(),
            'available' => Gown::where('status', 'available')->count(),
            'pending' => Reservation::where('status', 'pending')->count(),
            'confirmed' => Reservation::whereIn('status', ['confirmed', 'ready_for_pickup'])->count(),
            'rentals' => Reservation::whereIn('status', ['released', 'overdue'])->count(),
            'customers' => Customer::count(),
            'revenue' => Payment::where('status', 'verified')->sum('amount'),
            'reservations' => Reservation::with('customer')->latest()->take(5)->get(),
            'recentGowns' => Gown::latest()->take(4)->get(),
        ]);
    }
}
