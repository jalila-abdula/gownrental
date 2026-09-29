<x-app-layout>
    <div class="sb-page">
        <div class="sb-wrap">

            <div class="sb-heading">
                <div>
                    <h1>Handoffs & <em>returns.</em></h1>
                    <p>Record each gown release, return inspection, and cleaning handoff.</p>
                </div>
                <a class="sb-btn" href="{{ route($base . '.reservations') }}">Reservation desk</a>
            </div>

            @if(session('success'))
                <div class="sb-success">{{ session('success') }}</div>
            @endif

            <section class="sb-panel"><div class="sb-table-wrap"><table class="sb-table">
                <thead><tr><th>RESERVATION</th><th>CUSTOMER / GOWN</th><th>PICKUP / DUE BACK</th><th>HANDOFF</th><th>STATUS / ID</th><th>ACTION</th></tr></thead><tbody>
                @forelse($rentals as $rental)
                    <tr>
                        <td><strong>{{ $rental->reservation_code }}</strong></td>
                        <td><strong>{{ $rental->customer->full_name ?? 'Guest' }}</strong><small class="sb-cell-sub">{{ $rental->items->map(fn($item) => $item->gown?->name)->filter()->join(', ') }}</small></td>
                        <td>{{ $rental->pickup_date?->format('M d, Y') }}<small class="sb-cell-sub">Due {{ $rental->return_date?->format('M d, Y') }}</small></td>
                        <td>{{ $rental->gownRelease?->release_date?->format('M d, Y') ?? 'Not released' }}</td>
                        <td><span class="sb-status">{{ ucfirst(str_replace('_', ' ', $rental->status)) }}</span><small class="sb-cell-sub">ID: {{ ucfirst($rental->collateral_status ?? 'not received') }}{{ $rental->id_safe_slot ? ' · '.$rental->id_safe_slot : '' }}</small>@if($rental->physical_id_photo_path || $rental->government_id_photo_path)<a class="sb-text-link" target="_blank" rel="noopener" href="{{ route('reservations.collateral-photo', ['reservation' => $rental, 'type' => $rental->physical_id_photo_path ? 'physical' : 'digital']) }}">View ID photo</a>@endif</td>
                        <td>
                            @if(in_array($rental->status, ['confirmed', 'ready_for_pickup'], true))
                                <form method="POST" enctype="multipart/form-data" action="{{ route($base . '.rentals.release', $rental) }}" class="sb-rental-action">@csrf
                                    <label>Condition <select name="condition_before" required><option value="excellent">Excellent</option><option value="good" selected>Good</option><option value="fair">Fair</option><option value="damaged">Damaged</option></select></label>
                                    @if(!$rental->physical_id_photo_path)<label>Physical ID photo<input type="file" name="physical_id_photo" accept="image/jpeg,image/png,image/webp" capture="environment" required></label>@endif
                                    <label>Secure safe slot<input name="id_safe_slot" value="{{ $rental->id_safe_slot }}" placeholder="Safe slot" required></label>
                                    <input name="notes" placeholder="Handoff note (optional)"><button class="sb-small-btn">Record pickup</button>
                                </form>
                            @elseif(in_array($rental->status, ['released', 'overdue'], true))
                                <form method="POST" action="{{ route($base . '.rentals.return', $rental) }}" class="sb-rental-action">@csrf
                                    <label>Condition <select name="condition_after" required><option value="excellent">Excellent</option><option value="good" selected>Good</option><option value="fair">Fair</option><option value="damaged">Damaged</option></select></label>
                                    <input type="number" min="0" step="0.01" name="repair_cost" placeholder="Repair or replacement cost (PHP)">
                                    <input name="notes" placeholder="Inspection note"><button class="sb-small-btn">Record return</button>
                                </form>
                            @elseif(in_array($rental->status, ['returned', 'completed'], true) && $rental->collateral_status === 'held')
                                @if($rental->balance <= 0)
                                    <form method="POST" action="{{ route($base . '.rentals.release-id', $rental) }}">@csrf<button class="sb-small-btn">Release original ID</button></form>
                                @else
                                    <span class="sb-cell-sub">Keep ID in safe until ₱{{ number_format($rental->balance, 2) }} is paid.</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="sb-empty">There are no confirmed pickups or active rentals right now.</td></tr>
                @endforelse
                </tbody></table></div></section>
            <section class="sb-panel sb-cleaning-panel">
                <div class="sb-panel-head">
                    <div>
                        <h2>Gowns to clean</h2>
                        <p>Complete a cleaning check before returning a gown to the available collection.</p>
                    </div>
                    <span class="sb-pill">{{ $cleanings->count() }} waiting</span>
                </div>

                @forelse($cleanings as $cleaning)
                    <div class="sb-cleaning-row">
                        <div class="sb-gown-thumb">✧</div>
                        <div>
                            <b>{{ $cleaning->gown->name ?? 'Gown' }}</b>
                            <small>{{ $cleaning->cleaning_type }} · {{ $cleaning->notes }}</small>
                        </div>
                        <form method="POST" action="{{ route($base . '.cleaning.complete', $cleaning) }}">
                            @csrf
                            <button class="sb-small-btn">Mark clean & available</button>
                        </form>
                    </div>
                @empty
                    <div class="sb-empty">Everything is clean and ready.</div>
                @endforelse
            </section>

        </div>
    </div>
</x-app-layout>
