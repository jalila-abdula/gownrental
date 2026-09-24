<x-app-layout>
    <div class="sb-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div>
                    <span class="sb-kicker">COLLECTION CARE</span>
                    <h1>Gown <em>maintenance.</em></h1>
                    <p>Log repairs and inspections, then return ready pieces to the collection.</p>
                </div>
                <a class="sb-outline-btn" href="{{ route($base.'.rentals') }}">Rental operations</a>
            </div>

            @if(session('success'))
                <div class="sb-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="sb-form-errors">{{ $errors->first() }}</div>
            @endif

            <div class="sb-columns">
                <section class="sb-panel sb-form-panel">
                    <form method="POST" action="{{ route($base.'.maintenance.store') }}" class="sb-reservation-form">
                        @csrf
                        <h2>Log maintenance</h2>
                        <p class="sb-form-intro">The selected gown will be removed from the rentable collection until its work is complete.</p>
                        <label>Gown
                            <select name="gown_id" required>
                                <option value="">Choose a gown</option>
                                @foreach($gowns as $gown)
                                    <option value="{{ $gown->id }}" @selected(old('gown_id') == $gown->id)>{{ $gown->gown_code }} · {{ $gown->name }} ({{ ucfirst(str_replace('_', ' ', $gown->status)) }})</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Work type<input name="maintenance_type" value="{{ old('maintenance_type') }}" placeholder="Alteration, repair, inspection" required></label>
                        <label>Description<textarea name="description" rows="3" required>{{ old('description') }}</textarea></label>
                        <div class="sb-form-row">
                            <label>Work date<input type="date" name="maintenance_date" value="{{ old('maintenance_date', today()->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" required></label>
                            <label>Cost (PHP)<input type="number" name="cost" min="0" max="1000000" step="0.01" value="{{ old('cost', 0) }}"></label>
                        </div>
                        <label>Notes<input name="notes" value="{{ old('notes') }}" placeholder="Optional tracking note"></label>
                        <button class="sb-btn" type="submit">Save maintenance record</button>
                    </form>
                </section>

                <section class="sb-panel">
                    <div class="sb-panel-head">
                        <div><h2>Maintenance log</h2><p>{{ $maintenanceRecords->total() }} recorded jobs</p></div>
                    </div>
                    <div class="sb-team-list">
                        @forelse($maintenanceRecords as $record)
                            <article class="sb-team-row sb-maintenance-row">
                                <div class="sb-team-copy">
                                    <b>{{ $record->gown->name ?? 'Removed gown' }}</b>
                                    <small>{{ $record->maintenance_type }} · {{ $record->maintenance_date?->format('M d, Y') }}<br>{{ $record->description }}<br>Cost: ₱{{ number_format($record->cost, 2) }} · {{ ucfirst($record->status) }}</small>
                                    @if($record->notes)<small>{{ $record->notes }}</small>@endif
                                </div>
                                @if($record->status === 'pending')
                                    <details class="sb-employee-edit">
                                        <summary>Complete job</summary>
                                        <form method="POST" action="{{ route($base.'.maintenance.complete', $record) }}">
                                            @csrf
                                            <label>Final condition
                                                <select name="condition" required>
                                                    @foreach(['excellent', 'good', 'fair', 'damaged'] as $condition)
                                                        <option value="{{ $condition }}" @selected(($record->gown->condition ?? 'good') === $condition)>{{ ucfirst($condition) }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <label>Completion note<input name="completion_notes" placeholder="Optional repair outcome"></label>
                                            <button class="sb-small-btn">Close maintenance job</button>
                                        </form>
                                    </details>
                                @else
                                    <span class="sb-status">Completed</span>
                                @endif
                            </article>
                        @empty
                            <div class="sb-empty">No maintenance jobs have been logged.</div>
                        @endforelse
                    </div>
                    <div class="sb-pagination">{{ $maintenanceRecords->links() }}</div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
