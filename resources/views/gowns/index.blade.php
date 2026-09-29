<x-app-layout>
    <div class="sb-page sb-inventory-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div><span class="sb-kicker">INVENTORY · GOWNS</span><h1>Your gown <em>collection.</em></h1><p>Manage rentable pieces, their care, pricing, and included accessories.</p></div>
            </div>
            @if(session('success'))<div class="sb-success">{{ session('success') }}</div>@endif

            <div class="sb-stats sb-inventory-stats">
                <article class="sb-stat"><span>Total gowns</span><b>{{ $inventoryStats['total'] }}</b><small>All inventory</small></article>
                <article class="sb-stat"><span>Available</span><b>{{ $inventoryStats['available'] }}</b><small>Ready to reserve</small></article>
                <article class="sb-stat"><span>Rented</span><b>{{ $inventoryStats['rented'] }}</b><small>Currently with customers</small></article>
                <article class="sb-stat"><span>Maintenance</span><b>{{ $inventoryStats['maintenance'] }}</b><small>Cleaning, repair, or damage</small></article>
            </div>

            <section class="sb-panel">
                <div class="sb-inventory-content">
                    <div class="sb-inventory-toolbar">
                        <div><h2>All gowns</h2><p>{{ $gowns->total() }} {{ \Illuminate\Support\Str::plural('gown', $gowns->total()) }} in this view</p></div>
                    </div>
                    <form method="GET" class="sb-inventory-filter">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search gowns, code, or color" aria-label="Search gowns, code, or color">
                        <button class="sb-inventory-filter-submit" type="submit" aria-label="Search gowns">Search</button>
                        <select name="category" aria-label="Filter by category" onchange="this.form.requestSubmit()"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select>
                        <select name="status" aria-label="Filter by availability" onchange="this.form.requestSubmit()"><option value="">All statuses</option>@foreach(['available','reserved','rented','for_cleaning','under_maintenance','damaged','unavailable','retired'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select>
                        <select name="condition" aria-label="Filter by condition" onchange="this.form.requestSubmit()"><option value="">All conditions</option>@foreach(['excellent','good','fair','damaged'] as $condition)<option value="{{ $condition }}" @selected(request('condition') === $condition)>{{ ucfirst($condition) }}</option>@endforeach</select>
                        <button class="sb-inventory-primary sb-inventory-filter-add" type="button" onclick="document.getElementById('gown-create-dialog').showModal()">Add a gown <span aria-hidden="true">＋</span></button>
                    </form>
                    <div class="sb-inventory-table-wrap">
                        <table class="sb-inventory-table sb-gown-table"><thead><tr><th>Gown</th><th>Category</th><th>Size</th><th>Rental price</th><th>Condition</th><th>Availability</th><th>Bookings</th><th></th></tr></thead><tbody>
                            @forelse($gowns as $gown)
                                @php
                                    [$availabilityLabel, $availabilityClass] = match ($gown->status) {
                                        'available' => ['Available', 'is-available'],
                                        'reserved' => ['Reserved', 'is-reserved'],
                                        'rented' => ['Rented', 'is-rented'],
                                        'for_cleaning', 'under_maintenance' => ['Maintenance', 'is-maintenance'],
                                        'damaged' => ['Damaged', 'is-damaged'],
                                        default => ['Disabled', 'is-disabled'],
                                    };
                                @endphp
                                <tr>
                                    <td data-label="Gown">
                                        <div class="sb-inventory-name">
                                            <div class="sb-inventory-thumb">
                                                @if($gown->image)
                                                    <img src="{{ asset('storage/'.$gown->image) }}" alt="{{ $gown->name }}">
                                                @else
                                                    <span aria-hidden="true">GOWN</span>
                                                @endif
                                            </div>
                                            <span><b>{{ $gown->name }}</b><small>{{ $gown->gown_code }} · {{ ucfirst($gown->color ?: 'Color not set') }}</small></span>
                                        </div>
                                    </td>
                                    <td data-label="Category">{{ $gown->category->name ?? 'Uncategorized' }}</td>
                                    <td data-label="Size">{{ $gown->size ?: '—' }}</td>
                                    <td data-label="Rental price">₱{{ number_format($gown->rental_price, 2) }}</td>
                                    <td data-label="Condition"><span class="sb-inventory-status is-condition">{{ ucfirst($gown->condition) }}</span></td>
                                    <td data-label="Availability"><span class="sb-inventory-status sb-gown-status {{ $availabilityClass }}"><i aria-hidden="true"></i>{{ $availabilityLabel }}</span></td>
                                    <td data-label="Bookings">{{ $gown->bookings_count }}</td>
                                    <td data-label="Actions">
                                        <div class="sb-inventory-actions">
                                            <a href="{{ route('owner.gowns.show', $gown) }}">View</a>
                                            <a href="{{ route('owner.gowns.edit', $gown) }}">Edit</a>
                                            <details class="sb-inventory-more">
                                                <summary aria-label="More actions for {{ $gown->name }}" title="More actions">&#8943;</summary>
                                                @if($gown->status !== 'unavailable')
                                                    <form method="POST" action="{{ route('owner.gowns.destroy', $gown) }}" onsubmit="return confirm('Mark this gown unavailable?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="sb-retire">Disable gown</button>
                                                    </form>
                                                @endif
                                            </details>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8"><div class="sb-inventory-empty"><b>No gowns found</b><p>Try changing your filters or add your first gown.</p></div></td></tr>
                            @endforelse
                        </tbody></table>
                    </div>
                    <div class="sb-pagination">{{ $gowns->links() }}</div>
                </div>
            </section>

            <dialog class="sb-side-drawer" id="gown-create-dialog" aria-labelledby="gown-create-title"
                onclick="if (event.target === this) this.close()">
                <div class="sb-side-drawer-head">
                    <div>
                        <span class="sb-kicker">INVENTORY · GOWNS</span>
                        <h2 id="gown-create-title">Add a gown</h2>
                        <p>Enter the gown details, rental pricing, and included accessories.</p>
                    </div>
                    <button class="sb-side-drawer-close" type="button" aria-label="Close form"
                        onclick="document.getElementById('gown-create-dialog').close()">&times;</button>
                </div>
                <form method="POST" action="{{ route('owner.gowns.store') }}" enctype="multipart/form-data" class="sb-side-drawer-form">
                    @csrf
                    <input type="hidden" name="_inventory_drawer" value="gown">
                    @if($errors->any() && old('_inventory_drawer') === 'gown')
                        <div class="sb-form-errors">{{ $errors->first() }}</div>
                    @endif
                    @include('gowns._fields', ['gown' => null, 'isEditing' => false, 'inDrawer' => true])
                </form>
            </dialog>

            @if(old('_inventory_drawer') === 'gown')
                <script>document.getElementById('gown-create-dialog').showModal();</script>
            @endif
        </div>
    </div>
</x-app-layout>
