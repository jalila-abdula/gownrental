<x-app-layout>
    <div class="sb-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div>
                    <span class="sb-kicker">YOUR BOOKINGS</span>
                    <h1>My <em>reservations.</em></h1>
                    <p>View reservation status, rental dates, and payments.</p>
                </div>
                <a class="sb-btn" href="{{ route('customer.catalog') }}">Browse gowns</a>
            </div>

            @if(session('success'))
                <div class="sb-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="sb-form-errors">{{ $errors->first() }}</div>
            @endif

            <div class="sb-customer-bookings">
                @forelse($reservations as $reservation)
                    @php
                        $pendingAmount = $reservation->payments->where('status', 'pending')->sum('amount');
                        $payableNow = max(0, (float) $reservation->balance - $pendingAmount);
                    @endphp
                    <article class="sb-panel sb-customer-booking">
                        <div class="sb-customer-booking-head">
                            <div>
                                <span class="sb-kicker">{{ $reservation->reservation_code }}</span>
                                <h2>{{ $reservation->items->first()?->gown?->name ?? 'Gown booking' }}</h2>
                                <small>{{ $reservation->pickup_date?->toFormattedDateString() }} – {{ $reservation->return_date?->toFormattedDateString() }}</small>
                            </div>
                            <span class="sb-status">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                        </div>

                        <div class="sb-customer-booking-bottom">
                            <div><small>TOTAL</small><b>₱{{ number_format($reservation->grand_total, 2) }}</b></div>
                            <div><small>PAID</small><b>₱{{ number_format($reservation->amount_paid, 2) }}</b></div>
                            <div><small>BALANCE</small><b>₱{{ number_format($reservation->balance, 2) }}</b></div>
                            <div><small>PAYMENT STATUS</small><b>{{ $reservation->balance <= 0 ? 'Paid' : ($reservation->amount_paid > 0 ? 'Partially paid' : ($pendingAmount > 0 ? 'Proof under review' : 'Payment due')) }}</b></div>
                        </div>

                        @if(in_array($reservation->status, ['pending', 'awaiting_payment'], true))
                            <form method="POST" action="{{ route('customer.reservations.cancel', $reservation) }}" class="sb-cancel-booking" onsubmit="return confirm('Cancel this reservation request?')">
                                @csrf
                                @method('PATCH')
                                <button class="sb-small-btn sb-reject-btn" type="submit">Cancel request</button>
                            </form>
                        @endif

                        <details class="sb-customer-booking-details">
                            <summary>Payment details &amp; submit proof</summary>
                            <div class="sb-customer-payment-layout">
                                <div>
                                    <h3>Payment history</h3>
                                    @forelse($reservation->payments as $payment)
                                        <div class="sb-customer-payment-row">
                                            <span>
                                                <b>{{ $payment->payment_reference }}</b>
                                                <small>{{ strtoupper($payment->payment_method) }} · {{ $payment->created_at->toFormattedDateString() }}</small>
                                            </span>
                                            <span>
                                                ₱{{ number_format($payment->amount, 2) }} · {{ ucfirst($payment->status) }}
                                                @if($payment->proof_of_payment)
                                                    <a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener">View proof</a>
                                                @endif
                                            </span>
                                        </div>
                                    @empty
                                        <p class="sb-payment-empty">No payments submitted yet.</p>
                                    @endforelse
                                </div>

                                @if($payableNow > 0 && !in_array($reservation->status, ['cancelled', 'rejected', 'completed'], true))
                                    <form method="POST" action="{{ route('customer.reservations.payments.store', $reservation) }}" enctype="multipart/form-data" class="sb-customer-payment-form">
                                        @csrf
                                        <h3>Submit GCash payment</h3>
                                        <p>Upload a clear screenshot or receipt. Your balance changes after staff verify it.</p>
                                        <label>Payment type
                                            <select name="payment_type" required>
                                                <option value="downpayment">Down payment</option>
                                                <option value="rental_balance">Rental balance</option>
                                                <option value="security_deposit">Security deposit</option>
                                                <option value="penalty">Penalty</option>
                                            </select>
                                        </label>
                                        <label>Amount (up to ₱{{ number_format($payableNow, 2) }})
                                            <input type="number" name="amount" min="0.01" max="{{ $payableNow }}" step="0.01" required>
                                        </label>
                                        <label>Payment receipt
                                            <input type="file" name="proof" accept="image/jpeg,image/png,image/webp" required>
                                        </label>
                                        <button class="sb-small-btn">Submit proof for review</button>
                                    </form>
                                @endif
                            </div>
                        </details>
                    </article>
                @empty
                    <div class="sb-panel sb-empty">No reservations yet. Browse the catalog to request a gown.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
