<x-app-layout>
    <div class="sb-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div>
                    <h1>Reservation <em>desk.</em></h1>
                    <p>Review guest requests, update gown handoffs, and record payments.</p>
                </div>
                <a class="sb-btn" href="{{ route($base . '.dashboard') }}">Back to dashboard</a>
            </div>

            @if($errors->any())
                <div class="sb-form-errors">{{ $errors->first() }}</div>
            @endif
            @if(session('success'))
                <div class="sb-success">{{ session('success') }}</div>
            @endif

            <form class="sb-filterbar" method="GET">
                <input name="q" value="{{ request('q') }}" placeholder="Search guest or reservation">
                <select name="status">
                    <option value="">All statuses</option>
                    @foreach(['pending', 'awaiting_payment', 'confirmed', 'ready_for_pickup', 'released', 'returned', 'completed', 'cancelled', 'rejected', 'overdue'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="sb-btn" type="submit">Filter</button>
            </form>

            <section class="sb-panel"><div class="sb-table-wrap"><table class="sb-table"><thead><tr><th>RESERVATION</th><th>CUSTOMER</th><th>GOWN</th><th>DATES</th><th>TOTAL / BALANCE</th><th>STATUS & ACTIONS</th></tr></thead><tbody>
                @forelse($reservations as $reservation)
                    <tr>
                        <td><strong>{{ $reservation->reservation_code }}</strong><small class="sb-cell-sub">{{ $reservation->created_at?->format('M d, Y') }}</small></td>
                        <td><strong>{{ $reservation->customer->full_name ?? 'Guest' }}</strong><small class="sb-cell-sub">{{ $reservation->customer->contact_number ?? 'No contact' }}<br>{{ $reservation->customer->email ?? '' }}</small></td>
                        <td>{{ $reservation->items->map(fn($item) => $item->gown?->name)->filter()->join(', ') ?: 'Gown' }}</td>
                        <td>{{ $reservation->pickup_date?->format('M d, Y') }}<small class="sb-cell-sub">to {{ $reservation->return_date?->format('M d, Y') }}</small></td>
                        <td>₱{{ number_format($reservation->grand_total, 2) }}<small class="sb-cell-sub">Balance ₱{{ number_format($reservation->balance, 2) }}</small></td>
                        <td><span class="sb-status">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span><div class="sb-booking-actions">
                            <form method="POST" action="{{ route($base . '.reservations.update', $reservation) }}">
                                @csrf
                                @method('PATCH')
                                @php
                                    $statusChoices = match ($reservation->status) {
                                        'pending', 'awaiting_payment' => ['pending', 'confirmed', 'cancelled', 'rejected'],
                                        'confirmed' => ['confirmed', 'ready_for_pickup', 'cancelled', 'rejected'],
                                        'ready_for_pickup' => ['ready_for_pickup', 'cancelled'],
                                        'returned' => ['returned', 'completed'],
                                        default => [$reservation->status],
                                    };
                                @endphp
                                <select name="status">
                                    @foreach($statusChoices as $status)
                                        <option value="{{ $status }}" @selected($reservation->status === $status)>
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                                <input name="admin_notes" placeholder="Staff note (optional)"
                                    value="{{ $reservation->admin_notes }}">
                                <button class="sb-small-btn">Update booking</button>
                            </form>

                            @if($reservation->balance > 0)
                                <details class="sb-payment-details">
                                    <summary>Record cash payment</summary>
                                    <form method="POST" action="{{ route($base . '.payments.store', $reservation) }}">
                                        @csrf
                                        <input type="number" min="0.01" step="0.01" max="{{ $reservation->balance }}"
                                            name="amount" placeholder="Amount (PHP)" required>
                                        <select name="payment_type">
                                            <option value="downpayment">Down payment</option>
                                            <option value="rental_balance">Rental balance</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <select name="payment_method">
                                            <option value="cash">Cash</option>
                                            <option value="gcash">GCash</option>
                                            <option value="card">Card</option>
                                        </select>
                                        <button class="sb-small-btn">Save payment</button>
                                    </form>
                                </details>
                            @endif
                        </div></td></tr>
                @empty
                    <tr><td colspan="6" class="sb-empty">No reservations match your search.</td></tr>
                @endforelse
            </tbody></table></div></section>

            <div class="sb-pagination">{{ $reservations->links() }}</div>
        </div>
    </div>
</x-app-layout>
