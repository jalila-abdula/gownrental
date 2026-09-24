<x-app-layout>
    <div class="sb-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div><span class="sb-kicker">PAYMENTS & BALANCES</span><h1>Payment <em>ledger.</em></h1><p>Verify customer receipts and track recorded rental payments.</p></div>
                <a class="sb-btn" href="{{ route(auth()->user()->role.'.reservations') }}">Reservation desk</a>
            </div>

            @if(session('success'))<div class="sb-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="sb-form-errors">{{ $errors->first() }}</div>@endif

            <div class="sb-payment-ledger">
                @forelse($payments as $payment)
                    <article class="sb-panel sb-ledger-card">
                        <div>
                            <span class="sb-kicker">{{ $payment->payment_reference }}</span>
                            <h2>{{ $payment->customer->full_name ?? 'Customer' }}</h2>
                            <p>{{ $payment->reservation->reservation_code ?? 'Reservation' }} · {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</p>
                        </div>
                        <div class="sb-ledger-amount"><b>₱{{ number_format($payment->amount, 2) }}</b><small>{{ strtoupper($payment->payment_method) }} · {{ $payment->created_at->toFormattedDateString() }}</small></div>
                        <span class="sb-status">{{ ucfirst($payment->status) }}</span>

                        @if($payment->proof_of_payment)
                            <a class="sb-proof-link" href="{{ route('payments.proof', $payment) }}" target="_blank">View customer proof</a>
                        @endif

                        @if($payment->status === 'pending')
                            <form method="POST" action="{{ route($base.'.payments.update', $payment) }}" class="sb-review-payment">
                                @csrf @method('PATCH')
                                <input name="remarks" placeholder="Optional review note">
                                <button name="status" value="verified" class="sb-small-btn">Verify</button>
                                <button name="status" value="rejected" class="sb-small-btn sb-reject-btn">Reject</button>
                            </form>
                        @elseif($payment->remarks)
                            <small class="sb-ledger-note">{{ $payment->remarks }}</small>
                        @endif
                    </article>
                @empty
                    <div class="sb-panel sb-empty">No payment records yet. Record a cash payment from a reservation or wait for customer GCash submissions.</div>
                @endforelse
            </div>

            <div class="sb-pagination">{{ $payments->links() }}</div>
        </div>
    </div>
</x-app-layout>
