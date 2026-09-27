<x-app-layout>

    <div class="sb-page">
        <div class="sb-wrap">

            {{-- =========================
            PAGE HEADER
            ========================== --}}
            <div class="sb-heading">

                <div>
                    <h1>Payment <em>records.</em></h1>

                    <p>
                        Verify customer receipts and track recorded rental payments.
                    </p>
                </div>

                <div class="sb-heading-actions">

                    <a class="sb-btn" href="{{ route(auth()->user()->role . '.dashboard') }}">
                        ← Dashboard
                    </a>

                </div>

            </div>


            {{-- =========================
            SUCCESS MESSAGE
            ========================== --}}
            @if(session('success'))
                <div class="sb-success">
                    {{ session('success') }}
                </div>
            @endif


            {{-- =========================
            ERROR MESSAGE
            ========================== --}}
            @if($errors->any())
                <div class="sb-form-errors">
                    {{ $errors->first() }}
                </div>
            @endif


            {{-- PAYMENT SUMMARY--}}
            @php
                $totalAmount = $payments->sum('amount');

                $paidAmount = $payments
                    ->whereIn('status', ['verified', 'paid'])
                    ->sum('amount');

                $pendingAmount = $payments
                    ->where('status', 'pending')
                    ->sum('amount');

                $pendingCount = $payments
                    ->where('status', 'pending')
                    ->count();
            @endphp


            <div class="sb-payment-summary">

                {{-- Total --}}
                <div class="sb-summary-card">

                    <div class="sb-summary-icon">
                        ₱
                    </div>

                    <div>
                        <span>Total recorded</span>

                        <strong>
                            ₱{{ number_format($totalAmount, 2) }}
                        </strong>

                        <small>
                            All payment records
                        </small>
                    </div>

                </div>


                {{-- Verified --}}
                <div class="sb-summary-card">

                    <div class="sb-summary-icon sb-icon-green">
                        ✓
                    </div>

                    <div>
                        <span>Verified</span>

                        <strong>
                            ₱{{ number_format($paidAmount, 2) }}
                        </strong>

                        <small>
                            Confirmed payments
                        </small>
                    </div>

                </div>


                {{-- Pending --}}
                <div class="sb-summary-card">

                    <div class="sb-summary-icon sb-icon-gold">
                        ◷
                    </div>

                    <div>
                        <span>Pending</span>

                        <strong>
                            ₱{{ number_format($pendingAmount, 2) }}
                        </strong>

                        <small>
                            {{ $pendingCount }} payment{{ $pendingCount == 1 ? '' : 's' }} for review
                        </small>
                    </div>

                </div>

            </div>


            {{-- PAYMENT RECORDS HEADER --}}


            {{-- =========================
            PAYMENT RECORDS
            ========================== --}}
            <div class="sb-payment-ledger">

                @forelse($payments as $payment)

                    <article class="sb-panel sb-ledger-card">

                        {{-- LEFT --}}
                        <div class="sb-ledger-main">

                            <div class="sb-payment-reference">
                                {{ $payment->payment_reference }}
                            </div>

                            <h2>
                                {{ $payment->customer->full_name ?? 'Customer' }}
                            </h2>

                            <p>
                                {{ $payment->reservation->reservation_code ?? 'Reservation' }}

                                <span>•</span>

                                {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                            </p>

                        </div>


                        {{-- AMOUNT --}}
                        <div class="sb-ledger-amount">

                            <span>Amount</span>

                            <strong>
                                ₱{{ number_format($payment->amount, 2) }}
                            </strong>

                            <small>
                                {{ strtoupper($payment->payment_method) }}
                                ·
                                {{ $payment->created_at->toFormattedDateString() }}
                            </small>

                        </div>


                        {{-- STATUS --}}
                        <div class="sb-ledger-status">

                            @php
                                $statusClass = match ($payment->status) {
                                    'verified', 'paid' => 'sb-status-verified',
                                    'pending' => 'sb-status-pending',
                                    'rejected' => 'sb-status-rejected',
                                    default => 'sb-status-default',
                                };
                            @endphp

                            <span class="sb-status {{ $statusClass }}">
                                {{ ucfirst($payment->status) }}
                            </span>

                        </div>


                        {{-- PROOF --}}
                        @if($payment->proof_of_payment)

                            <a class="sb-proof-link" href="{{ route('payments.proof', $payment) }}" target="_blank">
                                View proof →
                            </a>

                        @endif


                        {{-- PENDING REVIEW --}}
                        @if($payment->status === 'pending')

                            <div class="sb-review-payment">

                                <form method="POST" action="{{ route($base . '.payments.update', $payment) }}">

                                    @csrf
                                    @method('PATCH')

                                    <input type="text" name="remarks" placeholder="Optional review note">

                                    <div class="sb-review-actions">

                                        <button type="submit" name="status" value="verified" class="sb-small-btn">
                                            ✓ Verify
                                        </button>

                                        <button type="submit" name="status" value="rejected" class="sb-small-btn sb-reject-btn">
                                            Reject
                                        </button>

                                    </div>

                                </form>

                            </div>

                        @elseif($payment->remarks)

                            <div class="sb-ledger-note">
                                <strong>Note:</strong>
                                {{ $payment->remarks }}
                            </div>

                        @endif

                    </article>

                @empty

                    {{-- EMPTY STATE --}}
                    <div class="sb-panel sb-empty">

                        <div class="sb-empty-icon">
                            ₱
                        </div>

                        <h2>
                            No payment records yet
                        </h2>

                        <p>
                            Payment transactions will appear here once you record
                            a cash payment or a customer submits a GCash payment.
                        </p>

                        <a class="sb-btn" href="{{ route(auth()->user()->role . '.reservations') }}">
                            Record a payment
                        </a>

                    </div>

                @endforelse

            </div>


            {{-- =========================
            PAGINATION
            ========================== --}}
            @if($payments->hasPages())

                <div class="sb-pagination">
                    {{ $payments->links() }}
                </div>

            @endif

        </div>
    </div>


    {{-- =========================
    PAGE STYLES
    ========================== --}}
    <style>
        /* =========================================
           PAGE
        ========================================= */

        .sb-page {
            min-height: 100vh;
            background: #fcf8f3;
            color: #4a3639;
        }

        .sb-wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 52px 48px 70px;
        }


        /* =========================================
           HEADER
        ========================================= */

        .sb-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 30px;
            margin-bottom: 38px;
        }

        .sb-heading h1 {
            margin: 0;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 46px;
            line-height: 1.05;
            font-weight: 600;
            letter-spacing: -1.5px;
            color: #641d35;
        }

        .sb-heading h1 em {
            color: #ad6673;
            font-weight: 400;
        }

        .sb-heading p {
            margin: 12px 0 0;
            color: #94756a;
            font-size: 15px;
        }

        .sb-heading-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }


        /* =========================================
           BUTTONS
        ========================================= */

        .sb-btn,
        .sb-outline-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 20px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .sb-btn {
            background: #641d35;
            color: #ffffff;
            border: 1px solid #641d35;
            box-shadow: 0 8px 18px rgba(100, 29, 53, 0.12);
        }

        .sb-btn:hover {
            background: #50162a;
            border-color: #50162a;
        }

        .sb-outline-btn {
            background: #fffdf9;
            color: #641d35;
            border: 1px solid #e4d5c7;
        }

        .sb-outline-btn:hover {
            background: #f8eee3;
            border-color: #d7c0ad;
        }


        /* =========================================
           ALERTS
        ========================================= */

        .sb-success,
        .sb-form-errors {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .sb-success {
            background: #edf7ef;
            color: #356440;
            border: 1px solid #d6ead9;
        }

        .sb-form-errors {
            background: #fff0f0;
            color: #8a3541;
            border: 1px solid #f0d1d4;
        }


        /* =========================================
           SUMMARY CARDS
        ========================================= */

        .sb-payment-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 48px;
        }

        .sb-summary-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #fffdf9;
            border: 1px solid #eaded2;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(83, 52, 42, 0.045);
        }

        .sb-summary-icon {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1e1d0;
            color: #641d35;
            font-family: Georgia, serif;
            font-size: 20px;
        }

        .sb-icon-green {
            background: #e7f2e8;
            color: #47734f;
        }

        .sb-icon-gold {
            background: #f5ead3;
            color: #9b7639;
        }

        .sb-summary-card span {
            display: block;
            margin-bottom: 4px;
            color: #9a8076;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sb-summary-card strong {
            display: block;
            color: #5d2939;
            font-family: Georgia, serif;
            font-size: 23px;
            font-weight: 600;
        }

        .sb-summary-card small {
            display: block;
            margin-top: 3px;
            color: #a48e84;
            font-size: 11px;
        }


        /* =========================================
           SECTION TITLE
        ========================================= */

        .sb-section-heading {
            margin-bottom: 17px;
        }

        .sb-section-kicker {
            color: #a27668;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.8px;
        }

        .sb-section-heading h2 {
            margin: 5px 0 2px;
            color: #5d2939;
            font-family: Georgia, serif;
            font-size: 25px;
            font-weight: 600;
        }

        .sb-section-heading p {
            margin: 0;
            color: #987e74;
            font-size: 13px;
        }


        /* =========================================
           PAYMENT CARD
        ========================================= */

        .sb-payment-ledger {
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .sb-panel {
            background: #fffdf9;
            border: 1px solid #eaded2;
            border-radius: 14px;
            box-shadow: 0 7px 24px rgba(83, 52, 42, 0.04);
        }

        .sb-ledger-card {
            display: grid;
            grid-template-columns: 1.7fr 1fr auto;
            gap: 22px;
            align-items: center;
            padding: 23px 25px;
        }

        .sb-ledger-main h2 {
            margin: 5px 0 5px;
            color: #542b35;
            font-family: Georgia, serif;
            font-size: 18px;
            font-weight: 600;
        }

        .sb-ledger-main p {
            margin: 0;
            color: #927970;
            font-size: 12px;
        }

        .sb-ledger-main p span {
            margin: 0 5px;
            color: #c4aaa0;
        }

        .sb-payment-reference {
            color: #a27668;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }


        /* =========================================
           AMOUNT
        ========================================= */

        .sb-ledger-amount {
            text-align: right;
        }

        .sb-ledger-amount span {
            display: block;
            margin-bottom: 3px;
            color: #aa9086;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sb-ledger-amount strong {
            display: block;
            color: #641d35;
            font-family: Georgia, serif;
            font-size: 20px;
            font-weight: 600;
        }

        .sb-ledger-amount small {
            display: block;
            margin-top: 4px;
            color: #a28a80;
            font-size: 10px;
        }


        /* =========================================
           STATUS
        ========================================= */

        .sb-ledger-status {
            text-align: center;
        }

        .sb-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .sb-status-verified {
            background: #e8f3e9;
            color: #47734f;
        }

        .sb-status-pending {
            background: #f7ecd8;
            color: #987332;
        }

        .sb-status-rejected {
            background: #fae7e8;
            color: #984452;
        }

        .sb-status-default {
            background: #eee8e4;
            color: #76635d;
        }


        /* =========================================
           PROOF LINK
        ========================================= */

        .sb-proof-link {
            color: #7b3d50;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }

        .sb-proof-link:hover {
            text-decoration: underline;
        }


        /* =========================================
           REVIEW FORM
        ========================================= */

        .sb-review-payment {
            grid-column: 1 / -1;
            padding-top: 17px;
            margin-top: 2px;
            border-top: 1px solid #eee3da;
        }

        .sb-review-payment form {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .sb-review-payment input {
            flex: 1;
            min-width: 0;
            height: 38px;
            padding: 0 12px;
            border: 1px solid #e3d5ca;
            border-radius: 8px;
            background: #fffdfa;
            color: #594449;
            font-size: 12px;
            outline: none;
        }

        .sb-review-payment input:focus {
            border-color: #b77b87;
            box-shadow: 0 0 0 3px rgba(183, 123, 135, .1);
        }

        .sb-review-actions {
            display: flex;
            gap: 7px;
        }

        .sb-small-btn {
            height: 38px;
            padding: 0 15px;
            border: 0;
            border-radius: 8px;
            background: #641d35;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .sb-small-btn:hover {
            background: #50162a;
        }

        .sb-reject-btn {
            background: #f5e3e3;
            color: #8a3948;
        }

        .sb-reject-btn:hover {
            background: #efd2d4;
        }


        /* =========================================
           NOTE
        ========================================= */

        .sb-ledger-note {
            grid-column: 1 / -1;
            padding-top: 13px;
            border-top: 1px solid #eee3da;
            color: #8e766e;
            font-size: 11px;
        }

        .sb-ledger-note strong {
            color: #68414b;
        }


        /* =========================================
           EMPTY STATE
        ========================================= */

        .sb-empty {
            min-height: 270px;
            padding: 45px 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .sb-empty-icon {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 17px;
            border-radius: 50%;
            background: #f1e1d0;
            color: #641d35;
            font-family: Georgia, serif;
            font-size: 25px;
        }

        .sb-empty h2 {
            margin: 0 0 7px;
            color: #5d2939;
            font-family: Georgia, serif;
            font-size: 22px;
        }

        .sb-empty p {
            max-width: 520px;
            margin: 0 0 20px;
            color: #978078;
            font-size: 13px;
            line-height: 1.7;
        }


        /* =========================================
           PAGINATION
        ========================================= */

        .sb-pagination {
            margin-top: 25px;
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 900px) {

            .sb-wrap {
                padding: 35px 25px 55px;
            }

            .sb-heading {
                flex-direction: column;
            }

            .sb-payment-summary {
                grid-template-columns: 1fr;
            }

            .sb-ledger-card {
                grid-template-columns: 1fr 1fr;
            }

            .sb-ledger-status {
                text-align: left;
            }

        }


        @media (max-width: 600px) {

            .sb-wrap {
                padding: 28px 17px 45px;
            }

            .sb-heading h1 {
                font-size: 36px;
            }

            .sb-heading-actions {
                width: 100%;
            }

            .sb-heading-actions a {
                flex: 1;
            }

            .sb-ledger-card {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .sb-ledger-amount {
                text-align: left;
            }

            .sb-review-payment form {
                flex-direction: column;
                align-items: stretch;
            }

            .sb-review-actions {
                width: 100%;
            }

            .sb-small-btn {
                flex: 1;
            }

        }
    </style>

</x-app-layout>