<x-app-layout>
    <div class="sb-page sb-inventory-page"><div class="sb-wrap">
        <div class="sb-heading"><div><span class="sb-kicker">INVENTORY · STYLING PIECES</span><h1>Gown <em>accessories.</em></h1><p>Track the smaller pieces that complete a rental look.</p></div></div>
        @if(session('success'))<div class="sb-success">{{ session('success') }}</div>@endif
        <section class="sb-panel"><div class="sb-inventory-content">
            <div class="sb-inventory-toolbar"><div><h2>Accessory stock</h2><p>{{ $accessories->count() }} items</p></div><button class="sb-inventory-primary" type="button" onclick="document.getElementById('accessory-create-dialog').showModal()">Add accessory <span aria-hidden="true">＋</span></button></div>
            <div class="sb-inventory-table-wrap"><table class="sb-inventory-table"><thead><tr><th>Accessory</th><th>Stock</th><th>Linked gowns</th><th>Replacement cost</th><th>Status</th><th></th></tr></thead><tbody>
                @forelse($accessories as $accessory)
                    <tr><td><div class="sb-inventory-name"><div class="sb-inventory-thumb">✧</div><span><b>{{ $accessory->name }}</b><small>{{ $accessory->description ?: 'No description added' }}</small></span></div></td><td>{{ $accessory->quantity }}</td><td>{{ $accessory->gowns_count }}</td><td>₱{{ number_format($accessory->replacement_cost,2) }}</td><td><span class="sb-inventory-status {{ in_array($accessory->status,['damaged','unavailable']) ? 'is-damaged' : '' }}">{{ ucfirst($accessory->status) }}</span></td><td><div class="sb-inventory-actions"><a href="{{ route('owner.accessories.show',$accessory) }}">View</a><a href="{{ route('owner.accessories.edit',$accessory) }}">Edit</a>@if($accessory->status === 'available')<form method="POST" action="{{ route('owner.accessories.destroy',$accessory) }}" onsubmit="return confirm('Mark this accessory unavailable?')">@csrf @method('DELETE')<button class="sb-retire">Disable</button></form>@endif</div></td></tr>
                @empty
                    <tr><td colspan="6"><div class="sb-inventory-empty"><b>No accessories added</b><p>Add included styling pieces and link them to gowns when editing a gown.</p></div></td></tr>
                @endforelse
            </tbody></table></div>
        </div></section>

        <dialog class="sb-side-drawer" id="accessory-create-dialog" aria-labelledby="accessory-create-title"
            onclick="if (event.target === this) this.close()">
            <div class="sb-side-drawer-head">
                <div>
                    <span class="sb-kicker">INVENTORY · STYLING PIECES</span>
                    <h2 id="accessory-create-title">Add an accessory</h2>
                    <p>Record stock, availability, and replacement cost.</p>
                </div>
                <button class="sb-side-drawer-close" type="button" aria-label="Close form"
                    onclick="document.getElementById('accessory-create-dialog').close()">&times;</button>
            </div>
            <form method="POST" action="{{ route('owner.accessories.store') }}" class="sb-side-drawer-form">
                @csrf
                <input type="hidden" name="_inventory_drawer" value="accessory">
                @if($errors->any() && old('_inventory_drawer') === 'accessory')
                    <div class="sb-form-errors">{{ $errors->first() }}</div>
                @endif
                <label>Item name
                    <input name="name" value="{{ old('name') }}" maxlength="255" required>
                    <x-input-error :messages="$errors->get('name')" />
                </label>
                <label>Description
                    <textarea name="description" rows="3">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" />
                </label>
                <label>Quantity in stock
                    <input type="number" name="quantity" min="0" value="{{ old('quantity', 1) }}" required>
                    <x-input-error :messages="$errors->get('quantity')" />
                </label>
                <label>Replacement cost (PHP)
                    <input type="number" name="replacement_cost" min="0" step="0.01" value="{{ old('replacement_cost', 0) }}">
                    <x-input-error :messages="$errors->get('replacement_cost')" />
                </label>
                <label>Status
                    <select name="status" required>
                        @foreach(['available', 'unavailable', 'damaged', 'retired'] as $status)
                            <option value="{{ $status }}" @selected(old('status', 'available') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" />
                </label>
                <div class="sb-side-drawer-actions">
                    <button class="sb-side-drawer-cancel" type="button" onclick="document.getElementById('accessory-create-dialog').close()">Cancel</button>
                    <button class="sb-inventory-primary" type="submit">Create accessory</button>
                </div>
            </form>
        </dialog>

        @if(old('_inventory_drawer') === 'accessory')
            <script>document.getElementById('accessory-create-dialog').showModal();</script>
        @endif
    </div></div>
</x-app-layout>
