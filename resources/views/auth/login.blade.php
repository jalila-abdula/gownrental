<x-guest-layout>
 <div class="sb-auth-intro"><span class="sb-kicker">ACCOUNT ACCESS</span><h1>Sign in</h1><p>Enter your email and password to open your account.</p></div>
 <x-auth-session-status class="sb-auth-status" :status="session('status')" />
 <form method="POST" action="{{ route('login') }}" class="sb-login-form">@csrf
  <label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com"><x-input-error :messages="$errors->get('email')" class="sb-auth-error" />
  <div class="sb-pass-label"><label for="password">Password</label>@if(Route::has('password.request'))<a href="{{ route('password.request') }}">Forgot password?</a>@endif</div><div class="sb-password"><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"><button type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password'" aria-label="Show or hide password">◉</button></div><x-input-error :messages="$errors->get('password')" class="sb-auth-error" />
  <label class="sb-remember"><input type="checkbox" name="remember"> <span>Remember me</span></label><button class="sb-auth-submit" type="submit">Sign in <span>→</span></button>
 </form>
 @if(Route::has('register'))<div class="sb-auth-register">New to Shyra Beautique? <a href="{{ route('register') }}">Create an account</a></div>@endif
 <div class="sb-auth-roles"><span>ACCOUNT TYPES</span><div><i>Owner</i><i>Employee</i><i>Customer</i></div></div>
</x-guest-layout>
