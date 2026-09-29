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

            <div class="sb-stats sb-ops-summary">
                <article class="sb-stat"><span>Pickups today</span><b>{{ $pickupsToday }}</b><small>Confirmed bookings</small></article>
                <article class="sb-stat"><span>Active rentals</span><b>{{ $activeRentals }}</b><small>Currently with customers</small></article>
                <article class="sb-stat"><span>Returns due</span><b>{{ $returnsDue }}</b><small>Due today or overdue</small></article>
                <article class="sb-stat"><span>To clean</span><b>{{ $cleaningCount }}</b><small>Waiting for inspection</small></article>
            </div>

            <section class="sb-panel sb-table-section">
                <div class="sb-panel-head">
                    <div>
                        <h2>Pickup &amp; return queue</h2>
                        <p>Confirmed reservations, active rentals, and items due back.</p>
                    </div>
                    <span class="sb-pill">{{ $rentals->count() }} records</span>
                </div>
                <div class="sb-table-wrap sb-ops-table-wrap">
                    <table class="sb-table sb-ops-table sb-ops-table--rentals">
                        <thead>
                            <tr>
                                <th>BOOKING / CUSTOMER</th>
                                <th>GOWN</th>
                                <th>STATUS</th>
                                <th>RENTAL DATES</th>
                                <th>HANDOFF</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rentals as $rental)
                                <tr>
                                    <td data-label="Booking / customer">
                                        <strong>{{ $rental->reservation_code }}</strong>
                                        <small class="sb-cell-sub">{{ $rental->customer->full_name ?? 'Guest' }}</small>
                                        <small class="sb-cell-sub">{{ $rental->customer->contact_number ?? 'No contact' }}</small>
                                    </td>
                                    <td data-label="Gown">{{ $rental->items->map(fn($item) => $item->gown?->name)->filter()->join(', ') ?: 'Gown' }}</td>
                                    <td data-label="Status"><span class="sb-status">{{ ucfirst(str_replace('_', ' ', $rental->status)) }}</span></td>
                                    <td data-label="Rental dates">
                                        <strong>{{ $rental->pickup_date?->format('M d, Y') }}</strong>
                                        <small class="sb-cell-sub">Due {{ $rental->return_date?->format('M d, Y') }}</small>
                                    </td>
                                    <td data-label="Handoff">{{ $rental->gownRelease?->release_date?->format('M d, Y') ?? 'Not released' }}</td>
                                    <td data-label="Action">
                                        @if(in_array($rental->status, ['confirmed', 'ready_for_pickup'], true))
                                            <details class="sb-row-actions">
                                                <summary>Record pickup</summary>
                                                <form method="POST" action="{{ route($base . '.rentals.release', $rental) }}" class="sb-row-action-form">
                                                    @csrf
                                                    <label>Condition at handoff
                                                        <select name="condition_before" required>
                                                            <option value="excellent">Excellent</option>
                                                            <option value="good" selected>Good</option>
                                                            <option value="fair">Fair</option>
                                                            <option value="damaged">Damaged</option>
                                                        </select>
                                                    </label>
                                                    <label>Handoff note<input name="notes" placeholder="Optional note"></label>
                                                    <button class="sb-small-btn">Save pickup</button>
                                                </form>
                                            </details>
                                        @elseif(in_array($rental->status, ['released', 'overdue'], true))
                                            <details class="sb-row-actions">
                                                <summary>Record return</summary>
                                                <form method="POST" action="{{ route($base . '.rentals.return', $rental) }}" class="sb-row-action-form">
                                                    @csrf
                                                    <label>Condition on return
                                                        <select name="condition_after" required>
                                                            <option value="excellent">Excellent</option>
                                                            <option value="good" selected>Good</option>
                                                            <option value="fair">Fair</option>
                                                            <option value="damaged">Damaged</option>
                                                        </select>
                                                    </label>
                                                    <label>Repair fee if damaged<input type="number" min="0" step="0.01" name="repair_cost" placeholder="₱0.00"></label>
                                                    <label>Inspection note<input name="notes" placeholder="Condition or issue observed"></label>
                                                    <button class="sb-small-btn">Save return</button>
                                                </form>
                                            </details>
                                        @else
                                            <span class="sb-cell-sub">No action</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="sb-empty sb-empty-state"><strong>No active handoffs</strong><small>Confirmed pickups and active rentals will appear here.</small></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

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