<x-app-layout>
<div class="sb-page sb-collection-page"><div class="sb-wrap">
    <div class="sb-heading"><div><span class="sb-kicker">SHYRA BEAUTIQUE · THE COLLECTION</span><h1>Browse the <em>collection.</em></h1><p>View gown sizes, rental prices, and availability.</p></div><a class="sb-outline-btn" href="{{ route(auth()->user()->role.'.dashboard') }}">Back to home</a></div>
    <div class="sb-catalog-tools"><input id="gownSearch" type="search" placeholder="Search gowns and styles..."><select id="categoryFilter"><option value="">All categories</option>@foreach($gowns->pluck('category.name')->filter()->unique() as $category)<option value="{{ strtolower($category) }}">{{ $category }}</option>@endforeach</select><select id="sizeFilter"><option value="">All sizes</option>@foreach($gowns->pluck('size')->filter()->unique()->sort() as $size)<option value="{{ strtolower($size) }}">{{ $size }}</option>@endforeach</select><select id="styleFilter"><option value="">All styles</option>@foreach($gowns->pluck('style')->filter()->unique()->sort() as $style)<option value="{{ strtolower($style) }}">{{ $style }}</option>@endforeach</select><select id="colorFilter"><option value="">All colors</option>@foreach($gowns->pluck('color')->filter()->unique()->sort() as $color)<option value="{{ strtolower($color) }}">{{ $color }}</option>@endforeach</select><select id="availabilityFilter"><option value="">Any availability</option><option value="available">Available</option><option value="reserved">Reserved</option></select></div>
    <div class="sb-cards" id="gownCatalog">
        @forelse($gowns as $gown)
            <article class="sb-product" data-search="{{ strtolower($gown->name.' '.($gown->category->name ?? '')) }}" data-category="{{ strtolower($gown->category->name ?? '') }}" data-size="{{ strtolower($gown->size ?? '') }}" data-style="{{ strtolower($gown->style ?? '') }}" data-color="{{ strtolower($gown->color ?? '') }}" data-status="{{ $gown->status }}">
                <a class="sb-product-image @if(!$gown->image) is-empty @endif" href="{{ route(auth()->user()->role === 'customer' ? 'customer.gowns.show' : auth()->user()->role.'.catalog.show', $gown) }}" @if($gown->image) style="background-image:url('{{ asset('storage/'.$gown->image) }}')" @endif><span class="sb-available">{{ ucfirst(str_replace('_',' ',$gown->status)) }}</span></a>
                <div class="sb-product-info"><small>{{ $gown->category->name ?? 'THE COLLECTION' }} · SIZE {{ $gown->size ?? 'VARIOUS' }}</small><h3>{{ $gown->name }}</h3>
                    <p class="sb-product-meta">@if($gown->color)<span>{{ $gown->color }}</span>@endif @if($gown->style)<span>{{ $gown->style }}</span>@endif</p>
                    <a class="sb-product-details" href="{{ route(auth()->user()->role === 'customer' ? 'customer.gowns.show' : auth()->user()->role.'.catalog.show', $gown) }}">View details</a>
                    <div><b>₱{{ number_format($gown->rental_price, 0) }}</b><span>/ rental</span>
                        @if($gown->status === 'available' && auth()->user()->role === 'customer')<a href="{{ route('customer.reserve', $gown) }}">Reserve →</a>
                        @elseif($gown->status === 'available' && in_array(auth()->user()->role, ['owner', 'employee'], true))<a href="{{ route(auth()->user()->role.'.catalog.reserve', $gown) }}">Start reservation →</a>@endif
                    </div>
                </div>
            </article>
        @empty<div class="sb-panel sb-empty">No gowns are listed yet.</div>@endforelse
    </div>
</div></div>
<script>(() => {const f={search:gownSearch,category:categoryFilter,size:sizeFilter,style:styleFilter,color:colorFilter,status:availabilityFilter};Object.values(f).forEach(el=>el.addEventListener('input',()=>{const q=(f.search.value||'').toLowerCase();document.querySelectorAll('.sb-product').forEach(card=>{card.hidden=!(card.dataset.search.includes(q)&&(!f.category.value||card.dataset.category===f.category.value)&&(!f.size.value||card.dataset.size===f.size.value)&&(!f.style.value||card.dataset.style===f.style.value)&&(!f.color.value||card.dataset.color===f.color.value)&&(!f.status.value||card.dataset.status===f.status.value));});}));})();</script>
</x-app-layout>
