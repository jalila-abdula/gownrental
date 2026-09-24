<x-app-layout>
    <div class="sb-page">
        <div class="sb-wrap">
            @if(session('reservation_code'))
                <div class="sb-success">Reservation {{ session('reservation_code') }} received. It is waiting for staff confirmation.</div>
            @endif
            <section class="sb-customer-hero">
                <div>
                    <span class="sb-kicker">CUSTOMER DASHBOARD</span>
                    <h1>Browse the <em>collection.</em></h1>
                    <p>View gown sizes, rental prices, and availability.</p>
                    <a class="sb-btn" href="{{ route('customer.catalog') }}">Browse gowns <span>&rarr;</span></a>
                </div>
                <div class="sb-hero-orbit"><span>&#10047;</span><small>GOWN RENTALS</small></div>
            </section>

            @if($recent)
                <section class="sb-recent-booking">
                    <div><span class="sb-kicker">RECENT RESERVATION</span><b>{{ $recent->items->first()?->gown?->name ?? 'Gown reservation' }}</b><small>{{ $recent->reservation_code }} &middot; {{ $recent->pickup_date?->toFormattedDateString() }} - {{ $recent->return_date?->toFormattedDateString() }}</small></div>
                    <span class="sb-status">{{ ucfirst(str_replace('_', ' ', $recent->status)) }}</span>
                    <a href="{{ route('customer.reservations') }}">View reservation</a>
                </section>
            @endif

            <div class="sb-section-head"><div><span class="sb-kicker">THE COLLECTION</span><h2>Featured gowns</h2></div><a class="sb-text-link" href="{{ route('customer.catalog') }}">View all gowns &rarr;</a></div>
            <div class="sb-cards">
                @forelse($featured as $gown)
                    <article class="sb-product">
                        <div class="sb-product-image" @if($gown->image) style="background-image:url('{{ asset('storage/'.$gown->image) }}')" @endif><span class="sb-available">Available</span></div>
                        <div class="sb-product-info"><small>{{ $gown->category->name ?? 'GOWN' }}</small><h3>{{ $gown->name }}</h3><div><b>&#8369;{{ number_format($gown->rental_price, 0) }}</b><span>/ rental</span><a href="{{ route('customer.reserve', $gown) }}">Reserve &rarr;</a></div></div>
                    </article>
                @empty
                    <div class="sb-panel sb-empty">No gowns are available to display yet.</div>
                @endforelse
            </div>
            <div class="sb-reserve-banner"><div><span class="sb-kicker">RESERVATIONS</span><h2>Have an event date?</h2><p>Browse gowns and submit a request for your pickup and return dates.</p></div><a class="sb-btn sb-btn-light" href="{{ route('customer.catalog') }}">Open catalog &rarr;</a></div>
        </div>
    </div>
</x-app-layout>
