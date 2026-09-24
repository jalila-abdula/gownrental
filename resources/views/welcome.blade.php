<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Browse and rent gowns for weddings, debuts, proms, and formal events at Shyra Beautique.">
    <title>Gown rentals | Shyra Beautique</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|playfair-display:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sb-home">
    <header class="sb-home-nav">
        <a class="sb-brand" href="/"><span class="sb-brand-mark">S</span><span>Shyra <i>Beautique</i><small>GOWN RENTAL BOUTIQUE</small></span></a>
        <nav><a href="#collection">Collection</a><a href="#experience">How it works</a><a href="#about">About</a></nav>
        <div class="sb-home-actions"><a class="sb-home-login" href="{{ route('login') }}">Sign in</a><a class="sb-home-signup" href="{{ route('register') }}">Create account <span>→</span></a></div>
        <button class="sb-home-menu" onclick="document.querySelector('.sb-home-nav nav').classList.toggle('sb-nav-open')" aria-label="Open navigation">☰</button>
    </header>
    <main>
        <section class="sb-home-hero">
            <div class="sb-home-hero-copy">
                <span class="sb-kicker">GOWN RENTALS FOR EVENTS</span>
                <h1>Find a gown<br>for your next <em>event.</em></h1>
                <p>Browse the collection, check rental details, and send a request for your dates.</p>
                <div class="sb-home-hero-actions"><a class="sb-home-signup" href="#collection">Browse gowns <span>→</span></a><a class="sb-home-quiet" href="#experience"><span class="sb-play">⌄</span>How renting works</a></div>
                <div class="sb-home-note"><span>✓</span><div><b>Online reservations</b><small>Submit your dates and track the request from your account.</small></div></div>
            </div>
            <div class="sb-home-hero-art"><div class="sb-hero-photo"></div><div class="sb-photo-label"><span>SHYRA BEAUTIQUE</span><b>Gowns for rent</b></div><div class="sb-floating-seal"><span>✿</span><small>WEDDINGS<br>DEBUTS<br>FORMAL</small></div></div>
            <div class="sb-home-scroll">VIEW THE COLLECTION <span>↓</span></div>
        </section>
        <section class="sb-home-trust"><span>GOWNS FOR</span><div><i>Weddings</i><b>·</b><i>Debuts</i><b>·</b><i>Proms</i><b>·</b><i>Formal events</i></div></section>
        <section class="sb-home-collection" id="collection">
            <div class="sb-home-section-head"><div><span class="sb-kicker">THE COLLECTION</span><h2>Available <em>gowns.</em></h2></div><p>View gown sizes, rental prices, and current availability. Create an account to request dates.</p></div>
            <div class="sb-home-products">
                @forelse($featuredGowns as $gown)
                    <article class="sb-home-product"><div class="sb-home-product-image" style="--gown-image: url('{{ $gown->image ? asset('storage/'.$gown->image) : 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=900&q=85' }}')"><span>AVAILABLE</span></div><div class="sb-home-product-copy"><div><small>{{ $gown->category->name ?? 'GOWN' }}</small><h3>{{ $gown->name }}</h3></div><b>₱{{ number_format($gown->rental_price, 0) }}<small> / rental</small></b></div></article>
                @empty
                    <div class="sb-home-empty"><b>No gowns are listed yet.</b><span>Please check back later or contact the boutique for availability.</span></div>
                @endforelse
            </div>
            <div class="sb-home-center"><a class="sb-home-outline" href="{{ route('login') }}">Sign in to view the full collection <span>→</span></a></div>
        </section>
        <section class="sb-home-experience" id="experience"><div class="sb-experience-image"></div><div class="sb-experience-copy"><span class="sb-kicker">HOW IT WORKS</span><h2>Reserve a gown<br>in three <em>steps.</em></h2><p>Use your account to request a gown for your event dates. Staff will review availability and update your reservation.</p><div class="sb-experience-steps"><div><i>01</i><span><b>Browse</b><small>Compare gown sizes, styles, and prices.</small></span></div><div><i>02</i><span><b>Request dates</b><small>Choose your pickup and return dates.</small></span></div><div><i>03</i><span><b>Track the request</b><small>Check your reservation status and payment details.</small></span></div></div><a class="sb-home-signup" href="{{ route('register') }}">Create an account <span>→</span></a></div></section>
        <section class="sb-home-about" id="about"><span class="sb-about-flower">✿</span><span class="sb-kicker">ABOUT THE BOUTIQUE</span><h2>Shyra <em>Beautique.</em></h2><p>Occasionwear rentals for weddings, debuts, proms, and other formal events. Browse the gowns and contact the boutique if you need help with fit or rental details.</p><a href="{{ route('login') }}">Sign in to your account <span>→</span></a></section>
        <section class="sb-home-cta"><div><span class="sb-kicker">BROWSE AND RESERVE ONLINE</span><h2>Looking for a <em>gown?</em></h2><p>Create an account to view the collection and send a rental request.</p></div><a class="sb-home-signup" href="{{ route('register') }}">Create account <span>→</span></a></section>
    </main>
    <footer class="sb-home-footer"><a class="sb-brand" href="/"><span class="sb-brand-mark">S</span><span>Shyra <i>Beautique</i><small>GOWN RENTAL BOUTIQUE</small></span></a><p>Gown rentals for local events.</p><div><a href="{{ route('login') }}">Sign in</a><a href="{{ route('register') }}">Create account</a></div><small>© {{ now()->year }} Shyra Beautique.</small></footer>
</body>
</html>
