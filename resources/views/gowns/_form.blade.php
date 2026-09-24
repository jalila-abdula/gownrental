<x-app-layout>
    <div class="sb-page sb-inventory-page"><div class="sb-wrap">
        <div class="sb-heading"><div><span class="sb-kicker">INVENTORY · GOWNS</span><h1>{{ $formTitle }}<em>.</em></h1><p>Add the rental details, category, and optional pieces supplied with this look.</p></div><a class="sb-inventory-secondary" href="{{ route('owner.gowns.index') }}">Back to collection</a></div>
        @if($errors->any())<div class="sb-form-errors">{{ $errors->first() }}</div>@endif
        <section class="sb-panel sb-inventory-form-card">
            <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                @if($isEditing) @method('PUT') @endif
                <h2>Gown details</h2><p>Gown code: {{ $gown?->gown_code ?? 'Assigned automatically when saved' }}</p>
                <div class="sb-inventory-grid">
                    <label>Gown name<input name="name" value="{{ old('name',$gown?->name) }}" maxlength="255" required></label>
                    <label>Category<select name="category_id" required><option value="">Choose category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$gown?->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></label>
                    <label>Size<input name="size" value="{{ old('size',$gown?->size) }}" maxlength="50" required></label>
                    <label>Color<input name="color" value="{{ old('color',$gown?->color) }}" maxlength="100" required></label>
                    <label>Style<input name="style" value="{{ old('style',$gown?->style) }}" maxlength="255" placeholder="A-line, mermaid, ball gown"></label>
                    <label>Rental price (PHP)<input type="number" name="rental_price" value="{{ old('rental_price',$gown?->rental_price) }}" min="0" step="0.01" required></label>
                    <label>Refundable security deposit (PHP)<input type="number" name="security_deposit" value="{{ old('security_deposit',$gown?->security_deposit ?? 0) }}" min="0" step="0.01" required></label>
                    <label>Purchase price (PHP)<input type="number" name="purchase_price" value="{{ old('purchase_price',$gown?->purchase_price) }}" min="0" step="0.01"></label>
                    <label>Condition<select name="condition" required>@foreach(['excellent','good','fair','damaged'] as $condition)<option value="{{ $condition }}" @selected(old('condition',$gown?->condition ?? 'good')===$condition)>{{ ucfirst($condition) }}</option>@endforeach</select></label>
                    <label>Availability<select name="status" required>@foreach(['available','reserved','rented','for_cleaning','under_maintenance','damaged','unavailable','retired'] as $status)<option value="{{ $status }}" @selected(old('status',$gown?->status ?? 'available')===$status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select></label>
                    <label>Date purchased<input type="date" name="date_purchased" value="{{ old('date_purchased',$gown?->date_purchased?->format('Y-m-d')) }}"></label>
                    <label>Gown image<input type="file" name="image" accept="image/jpeg,image/png,image/webp">@if($gown?->image)<small>Uploading a new image replaces the current one.</small>@endif</label>
                </div>
                <label>Description<textarea name="description" rows="3">{{ old('description',$gown?->description) }}</textarea></label>
                <label>Measurements<textarea name="measurements" rows="3" placeholder="Bust, waist, length, and other fit details">{{ old('measurements',$gown?->measurements) }}</textarea></label>
                <div class="sb-inventory-accessories">
                    <h3>Included accessories</h3><p>Accessories are tracked separately, then linked here when included with this gown (for example a shawl, belt, or hairpiece).</p>
                    <div class="sb-inventory-check-grid">
                        @forelse($accessories as $accessory)
                            <label class="sb-inventory-check"><input type="checkbox" name="accessories[]" value="{{ $accessory->id }}" @checked(in_array($accessory->id, old('accessories',$gown?->accessories->pluck('id')->all() ?? [])))><span><b>{{ $accessory->name }}</b><small>{{ $accessory->quantity }} in stock</small></span></label>
                        @empty
                            <p>No available accessories yet. You can add them from Inventory → Accessories.</p>
                        @endforelse
                    </div>
                </div>
                <div class="sb-inventory-form-actions"><a class="sb-inventory-secondary" href="{{ route('owner.gowns.index') }}">Cancel</a><button class="sb-inventory-primary" type="submit">{{ $isEditing ? 'Save gown' : 'Add gown to collection' }}</button></div>
            </form>
        </section>
    </div></div>
</x-app-layout>
