<x-guest-layout>
    <div class="sb-auth-intro"><span class="sb-kicker">NEW CUSTOMER ACCOUNT</span><h1>Create an account</h1><p>Create an account to browse gowns and submit reservation requests.</p></div>
    <form method="POST" action="{{ route('register') }}" class="sb-login-form">@csrf
        <label for="name">Your name</label><input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Enter your full name"><x-input-error :messages="$errors->get('name')" class="sb-auth-error" />
        <label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com"><x-input-error :messages="$errors->get('email')" class="sb-auth-error" />
        <label for="password">Create a password</label><div class="sb-password"><input id="password" type="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters"><button type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password'" aria-label="Show or hide password">â—‰</button></div><x-input-error :messages="$errors->get('password')" class="sb-auth-error" />
        <label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Enter your password again"><x-input-error :messages="$errors->get('password_confirmation')" class="sb-auth-error" />
        <button class="sb-auth-submit sb-register-submit" type="submit">Create my account <span>â†’</span></button>
    </form>
    <div class="sb-auth-register">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
    <div class="sb-auth-roles"><span>ACCOUNT FEATURES</span><div><i>Browse gowns</i><i>Request dates</i><i>Track bookings</i></div></div>
</x-guest-layout>
