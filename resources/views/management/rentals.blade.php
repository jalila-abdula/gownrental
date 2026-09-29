<x-app-layout>
    <div class="sb-page">
        <div class="sb-wrap">

            <div class="sb-heading">
                <div>
                    <h1>Handoffs & <em>returns.</em></h1>
                    <p>Track gown pickups, returns, inspections, and cleaning.</p>
                </div>
                <a class="sb-btn" href="{{ route($base . '.reservations') }}">Reservation desk <span aria-hidden="true">&rarr;</span></a>
            </div>

            @if(session('success'))
                <div class="sb-success">{{ session('success') }}</div>
            @endif

            <div class="sb-ops-list">
                @forelse($rentals as $rental)
                    <article class="sb-panel sb-booking">

                        <div class="sb-booking-top">
                            <div>
                                <span class="sb-kicker">{{ $rental->reservation_code }}</span>
                                <h2>{{ $rental->customer->full_name ?? 'Guest' }}</h2>
                                <p>{{ $rental->items->map(fn($item) => $item->gown?->name)->filter()->join(', ') }}</p>
                            </div>
                            <span class="sb-status">{{ ucfirst(str_replace('_', ' ', $rental->status)) }}</span>
                        </div>

                        <div class="sb-booking-info">
                            <div>
                                <small>PICKUP</small>
                                <b>{{ $rental->pickup_date?->format('M d, Y') }}</b>
                            </div>
                            <div>
                                <small>DUE BACK</small>
                                <b>{{ $rental->return_date?->format('M d, Y') }}</b>
                            </div>
                            <div>
                                <small>HANDOFF</small>
                                <b>{{ $rental->gownRelease?->release_date?->format('M d, Y') ?? 'Not released' }}</b>
                            </div>
                        </div>

                        @if(in_array($rental->status, ['confirmed', 'ready_for_pickup'], true))
                            <form method="POST" action="{{ route($base . '.rentals.release', $rental) }}"
                                class="sb-rental-action">
                                @csrf
                                <h3>Release gown to customer</h3>
                                <div class="sb-rental-formrow">
                                    <label>Condition at handoff
                                        <select name="condition_before" required>
                                            <option value="excellent">Excellent</option>
                                            <option value="good" selected>Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="damaged">Damaged</option>
                                        </select>
                                    </label>
                                    <label>Handoff note
                                        <input name="notes" placeholder="Optional note">
                                    </label>
                                    <button class="sb-small-btn">Record pickup</button>
                                </div>
                            </form>
                        @elseif(in_array($rental->status, ['released', 'overdue'], true))
                            <form method="POST" action="{{ route($base . '.rentals.return', $rental) }}"
                                class="sb-rental-action">
                                @csrf
                                <h3>Inspect returned gown</h3>
                                <div class="sb-rental-formrow">
                                    <label>Condition on return
                                        <select name="condition_after" required>
                                            <option value="excellent">Excellent</option>
                                            <option value="good" selected>Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="damaged">Damaged</option>
                                        </select>
                                    </label>
                                    <label>Repair fee if damaged
                                        <input type="number" min="0" step="0.01" name="repair_cost" placeholder="₱0.00">
                                    </label>
                                    <label>Inspection note
                                        <input name="notes" placeholder="Condition or issue observed">
                                    </label>
                                    <button class="sb-small-btn">Record return</button>
                                </div>
                            </form>
                        @endif

                    </article>
                @empty
                    <div class="sb-panel sb-empty">There are no confirmed pickups or active rentals right now.</div>
                @endforelse
            </div>

            <section class="sb-panel sb-cleaning-panel">
                <div class="sb-panel-head">
                    <div>
                        <h2>Gowns to clean</h2>
                        <p>Complete a cleaning check before returning a gown to the available collection.</p>
                    </div>
                    <span class="sb-pill">{{ $cleanings->count() }} waiting</span>
                </div>

                <div class="sb-table-wrap sb-ops-table-wrap">
                    <table class="sb-table sb-ops-table sb-ops-table--cleaning">
                        <thead>
                            <tr><th>GOWN</th><th>CLEANING TYPE</th><th>DATE</th><th>NOTES</th><th>ACTION</th></tr>
                        </thead>
                        <tbody>
                            @forelse($cleanings as $cleaning)
                                <tr>
                                    <td data-label="Gown"><strong>{{ $cleaning->gown->name ?? 'Gown' }}</strong></td>
                                    <td data-label="Cleaning type">{{ $cleaning->cleaning_type }}</td>
                                    <td data-label="Date">{{ $cleaning->cleaning_date?->format('M d, Y') ?? '—' }}</td>
                                    <td data-label="Notes">{{ $cleaning->notes ?: '—' }}</td>
                                    <td data-label="Action">
                                        <form method="POST" action="{{ route($base . '.cleaning.complete', $cleaning) }}">
                                            @csrf
                                            <button class="sb-small-btn">Mark clean &amp; available</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="sb-empty sb-empty-state"><strong>Nothing to clean</strong><small>Returned gowns needing inspection or cleaning will appear here.</small></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
