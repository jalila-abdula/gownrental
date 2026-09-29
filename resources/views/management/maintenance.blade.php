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
                    <div class="sb-table-wrap"><table class="sb-table"><thead><tr><th>GOWN</th><th>WORK</th><th>DATE</th><th>COST / STATUS</th><th>ACTION</th></tr></thead><tbody>
                        @forelse($maintenanceRecords as $record)
                            <tr>
                                <td><strong>{{ $record->gown->name ?? 'Removed gown' }}</strong><small class="sb-cell-sub">{{ $record->gown->gown_code ?? '' }}</small></td>
                                <td>{{ $record->maintenance_type }}<small class="sb-cell-sub">{{ $record->description }} @if($record->notes)<br>{{ $record->notes }}@endif</small></td>
                                <td>{{ $record->maintenance_date?->format('M d, Y') }}</td>
                                <td>₱{{ number_format($record->cost, 2) }}<small class="sb-cell-sub">{{ ucfirst($record->status) }}</small></td>
                                <td>
                                    @if($record->status === 'pending')
                                        <details class="sb-employee-edit"><summary>Complete job</summary><form method="POST" action="{{ route($base.'.maintenance.complete', $record) }}">@csrf
                                            <label>Final condition<select name="condition" required>@foreach(['excellent', 'good', 'fair', 'damaged'] as $condition)<option value="{{ $condition }}" @selected(($record->gown->condition ?? 'good') === $condition)>{{ ucfirst($condition) }}</option>@endforeach</select></label>
                                            <label>Completion note<input name="completion_notes" placeholder="Optional repair outcome"></label><button class="sb-small-btn">Close maintenance job</button>
                                        </form></details>
                                    @else<span class="sb-status">Completed</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="sb-empty">No maintenance jobs have been logged.</td></tr>
                        @endforelse
                    </tbody></table></div>
                    <div class="sb-pagination">{{ $maintenanceRecords->links() }}</div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
