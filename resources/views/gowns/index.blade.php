<x-app-layout>
    <div class="sb-page sb-inventory-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div><span class="sb-kicker">INVENTORY · GOWNS</span><h1>Your gown <em>collection.</em></h1><p>Manage rentable pieces, their care, pricing, and included accessories.</p></div>
                <a class="sb-inventory-primary" href="{{ route('owner.gowns.create') }}">Add a gown <span>＋</span></a>
            </div>
            @if(session('success'))<div class="sb-success">{{ session('success') }}</div>@endif
            <div class="sb-inventory-note"><span>✦</span><div><b>Gown inventory</b>Each gown is one rentable piece. Categories organize the collection; accessories can be linked to gowns as included styling items.</div></div>
            <section class="sb-panel">
                <div class="sb-inventory-content">
                    <div class="sb-inventory-toolbar"><div><h2>All gowns</h2><p>{{ $gowns->total() }} pieces in this view</p></div></div>
                    <form method="GET" class="sb-inventory-filter">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search gown, code, or color">
                        <select name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select>
                        <select name="status"><option value="">All statuses</option>@foreach(['available','reserved','rented','for_cleaning','under_maintenance','damaged','unavailable','retired'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select>
                        <button type="submit">Apply filters</button>
                    </form>
                    <div class="sb-inventory-table-wrap">
                        <table class="sb-inventory-table"><thead><tr><th>Gown</th><th>Category</th><th>Size</th><th>Rental</th><th>Condition</th><th>Status</th><th></th></tr></thead><tbody>
                            @forelse($gowns as $gown)
                                <tr>
                                    <td><div class="sb-inventory-name"><div class="sb-inventory-thumb" @if($gown->image) style="background-image:url('{{ asset('storage/'.$gown->image) }}')" @endif>✧</div><span><b>{{ $gown->name }}</b><small>{{ $gown->gown_code }} · {{ $gown->color ?: 'Color not set' }}</small></span></div></td>
                                    <td>{{ $gown->category->name ?? 'Uncategorized' }}</td><td>{{ $gown->size ?: '—' }}</td><td>₱{{ number_format($gown->rental_price,2) }}</td>
                                    <td><span class="sb-inventory-status">{{ ucfirst($gown->condition) }}</span></td><td><span class="sb-inventory-status {{ in_array($gown->status,['damaged','retired','unavailable']) ? 'is-damaged' : '' }}">{{ str_replace('_',' ',$gown->status) }}</span></td>
                                    <td><div class="sb-inventory-actions"><a href="{{ route('owner.gowns.show',$gown) }}">View</a><a href="{{ route('owner.gowns.edit',$gown) }}">Edit</a>@if($gown->status !== 'unavailable')<form method="POST" action="{{ route('owner.gowns.destroy',$gown) }}" onsubmit="return confirm('Mark this gown unavailable?')">@csrf @method('DELETE')<button class="sb-retire">Disable</button></form>@endif</div></td>
                                </tr>
                            @empty
                                <tr><td colspan="7"><div class="sb-inventory-empty"><b>No gowns found</b><p>Try changing your filters or add your first gown.</p><a class="sb-inventory-primary" href="{{ route('owner.gowns.create') }}">Add a gown</a></div></td></tr>
                            @endforelse
                        </tbody></table>
                    </div>
                    <div class="sb-pagination">{{ $gowns->links() }}</div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
