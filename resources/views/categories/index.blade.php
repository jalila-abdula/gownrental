<x-app-layout>
    <div class="sb-page sb-inventory-page"><div class="sb-wrap">
        <div class="sb-heading"><div><span class="sb-kicker">INVENTORY · ORGANIZATION</span><h1>Gown <em>categories.</em></h1><p>Group gowns into clear collections so staff and customers can browse them easily.</p></div><a class="sb-inventory-primary" href="{{ route('owner.categories.create') }}">Add category <span>＋</span></a></div>
        @if(session('success'))<div class="sb-success">{{ session('success') }}</div>@endif
        <div class="sb-inventory-note"><span>✦</span><div><b>What is a category?</b>A category labels the kind of gown, such as bridal, evening, prom, or formal. Assign one category to each gown to keep your collection organized and filterable.</div></div>
        <section class="sb-panel"><div class="sb-inventory-content">
            <div class="sb-inventory-toolbar"><div><h2>Categories</h2><p>{{ $categories->count() }} groups</p></div></div>
            <div class="sb-inventory-table-wrap"><table class="sb-inventory-table"><thead><tr><th>Category</th><th>Description</th><th>Gowns</th><th>Status</th><th></th></tr></thead><tbody>
                @forelse($categories as $category)
                    <tr><td><div class="sb-inventory-name"><div class="sb-inventory-thumb">⌑</div><span><b>{{ $category->name }}</b><small>Collection category</small></span></div></td><td>{{ $category->description ?: 'No description added' }}</td><td>{{ $category->gowns_count }}</td><td><span class="sb-inventory-status {{ $category->is_active ? '' : 'is-inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td><td><div class="sb-inventory-actions"><a href="{{ route('owner.categories.show',$category) }}">View</a><a href="{{ route('owner.categories.edit',$category) }}">Edit</a>@if($category->is_active)<form method="POST" action="{{ route('owner.categories.destroy',$category) }}" onsubmit="return confirm('Deactivate this category?')">@csrf @method('DELETE')<button class="sb-retire">Deactivate</button></form>@endif</div></td></tr>
                @empty
                    <tr><td colspan="5"><div class="sb-inventory-empty"><b>No categories yet</b><p>Create categories to make the collection easier to browse.</p><a class="sb-inventory-primary" href="{{ route('owner.categories.create') }}">Add a category</a></div></td></tr>
                @endforelse
            </tbody></table></div>
        </div></section>
    </div></div>
</x-app-layout>
