<x-app-layout>
    <div class="sb-page sb-inventory-page"><div class="sb-wrap">
        <div class="sb-heading"><div><span class="sb-kicker">INVENTORY · ORGANIZATION</span><h1>Gown <em>categories.</em></h1><p>Group gowns into clear collections so staff and customers can browse them easily.</p></div></div>
        @if(session('success'))<div class="sb-success">{{ session('success') }}</div>@endif
        <section class="sb-panel"><div class="sb-inventory-content">
            <div class="sb-inventory-toolbar"><div><h2>Categories</h2><p>{{ $categories->count() }} groups</p></div><button class="sb-inventory-primary" type="button" onclick="document.getElementById('category-create-dialog').showModal()">Add category <span aria-hidden="true">＋</span></button></div>
            <div class="sb-inventory-table-wrap"><table class="sb-inventory-table"><thead><tr><th>Category</th><th>Description</th><th>Gowns</th><th>Status</th><th></th></tr></thead><tbody>
                @forelse($categories as $category)
                    <tr><td><div class="sb-inventory-name"><div class="sb-inventory-thumb">⌑</div><span><b>{{ $category->name }}</b><small>Collection category</small></span></div></td><td>{{ $category->description ?: 'No description added' }}</td><td>{{ $category->gowns_count }}</td><td><span class="sb-inventory-status {{ $category->is_active ? '' : 'is-inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td><td><div class="sb-inventory-actions"><a href="{{ route('owner.categories.show',$category) }}">View</a><a href="{{ route('owner.categories.edit',$category) }}">Edit</a>@if($category->is_active)<form method="POST" action="{{ route('owner.categories.destroy',$category) }}" onsubmit="return confirm('Deactivate this category?')">@csrf @method('DELETE')<button class="sb-retire">Deactivate</button></form>@endif</div></td></tr>
                @empty
                    <tr><td colspan="5"><div class="sb-inventory-empty"><b>No categories yet</b><p>Create categories to make the collection easier to browse.</p></div></td></tr>
                @endforelse
            </tbody></table></div>
        </div></section>

        <dialog class="sb-side-drawer" id="category-create-dialog" aria-labelledby="category-create-title"
            onclick="if (event.target === this) this.close()">
            <div class="sb-side-drawer-head">
                <div>
                    <span class="sb-kicker">INVENTORY · ORGANIZATION</span>
                    <h2 id="category-create-title">Add a category</h2>
                    <p>Create a collection group for the gown catalog.</p>
                </div>
                <button class="sb-side-drawer-close" type="button" aria-label="Close form"
                    onclick="document.getElementById('category-create-dialog').close()">&times;</button>
            </div>
            <form method="POST" action="{{ route('owner.categories.store') }}" class="sb-side-drawer-form">
                @csrf
                <input type="hidden" name="_inventory_drawer" value="category">
                @if($errors->any() && old('_inventory_drawer') === 'category')
                    <div class="sb-form-errors">{{ $errors->first() }}</div>
                @endif
                <label>Category name
                    <input name="name" value="{{ old('name') }}" maxlength="255" placeholder="Evening gowns" required>
                    <x-input-error :messages="$errors->get('name')" />
                </label>
                <label>Description
                    <textarea name="description" rows="4" placeholder="What kinds of gowns belong in this collection?">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" />
                </label>
                <div class="sb-side-drawer-actions">
                    <button class="sb-side-drawer-cancel" type="button" onclick="document.getElementById('category-create-dialog').close()">Cancel</button>
                    <button class="sb-inventory-primary" type="submit">Create category</button>
                </div>
            </form>
        </dialog>

        @if(old('_inventory_drawer') === 'category')
            <script>document.getElementById('category-create-dialog').showModal();</script>
        @endif
    </div></div>
</x-app-layout>
