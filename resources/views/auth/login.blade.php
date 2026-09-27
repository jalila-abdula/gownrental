<x-guest-layout>

    <div class="min-h-screen flex items-center justify-center p-5 md:p-8 relative overflow-hidden">

        <!-- Background Decorations -->
        <div class="absolute inset-0 overflow-hidden auth-decoration">

            <!-- Large faded S -->
            <div
                class="absolute -left-10 bottom-0 text-[22rem] leading-none font-display font-bold text-[#5C1A2B]/[0.025]">
                S
            </div>

            <!-- Decorative circles -->
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full border border-[#B76E79]/20"></div>
            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full border border-[#B76E79]/15"></div>

            <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full border border-[#B76E79]/20"></div>

            <!-- Small floating sparkles -->
            <div class="auth-float absolute top-24 left-[12%] text-[#B76E79]/50 text-2xl">
                ✦
            </div>

            <div class="auth-float-slow absolute top-[35%] right-[8%] text-[#C9A46A]/60 text-xl">
                ✧
            </div>

            <div class="auth-float absolute bottom-24 right-[18%] text-[#B76E79]/40 text-2xl">
                ✦
            </div>

            <!-- Small hearts -->
            <div class="absolute top-[18%] right-[20%] text-[#B76E79]/30 text-lg">
                ♡
            </div>

            <div class="absolute bottom-[20%] left-[18%] text-[#B76E79]/30 text-lg">
                ♡
            </div>

        </div>


        <!-- Login Card -->
        <div
            class="relative z-10 w-full max-w-5xl bg-white rounded-[2rem] overflow-hidden shadow-[0_25px_70px_rgba(92,26,43,0.12)] grid grid-cols-1 md:grid-cols-2">

            <!-- LEFT SIDE -->
            <div
                class="relative overflow-hidden bg-[#5C1A2B] text-white min-h-[600px] flex flex-col justify-between p-8 md:p-12">

                <!-- Decorative background -->
                <div class="absolute inset-0 auth-decoration">

                    <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-white/[0.04]"></div>

                    <div class="absolute -bottom-32 -left-20 w-80 h-80 rounded-full border border-white/[0.08]"></div>

                    <div class="absolute top-1/2 -right-16 w-40 h-40 rounded-full border border-white/[0.06]"></div>

                    <!-- Big faded S -->
                    <div
                        class="absolute -bottom-16 -right-4 text-[18rem] leading-none font-display font-bold text-white/[0.035]">
                        S
                    </div>

                </div>


                <!-- Logo / Brand -->
                <div class="relative z-10">

                    <div class="flex items-center gap-3">

                        <div
                            class="w-12 h-12 rounded-full border border-white/40 flex items-center justify-center bg-white/10">
                            <span class="font-display text-2xl font-semibold">
                                S
                            </span>
                        </div>

                        <div>
                            <div class="font-display text-xl tracking-wide">
                                Shyra Beautique
                            </div>

                            <div class="text-[9px] tracking-[0.25em] uppercase text-white/60">
                                Gown Reservation & Rental
                            </div>
                        </div>

                    </div>

                </div>


                <!-- Main Message -->
                <div class="relative z-10 max-w-md">

                    <div class="flex items-center gap-3 mb-5">

                        <span class="h-px w-10 bg-[#C9A46A]"></span>

                        <span class="text-xs tracking-[0.3em] uppercase text-[#E5CFA5]">
                            Welcome Back
                        </span>

                        <span class="text-[#C9A46A]">
                            ✦
                        </span>

                    </div>


                    <h1 class="font-display text-4xl md:text-5xl leading-tight mb-5">
                        Your perfect gown
                        <span class="italic text-[#E5CFA5]">
                            awaits.
                        </span>
                    </h1>


                    <p class="text-white/70 leading-relaxed text-sm md:text-base">
                        Sign in to manage your reservations, browse elegant gowns,
                        and enjoy a simple rental experience with Shyra Beautique.
                    </p>


                    <!-- Decorative line -->
                    <div class="mt-8 flex items-center gap-3 text-white/30">
                        <span class="text-lg">♡</span>
                        <span class="h-px w-20 bg-white/20"></span>
                        <span class="text-lg">✦</span>
                        <span class="h-px w-20 bg-white/20"></span>
                        <span class="text-lg">♡</span>
                    </div>

                </div>


                <!-- Bottom label -->
                <div class="relative z-10 text-xs text-white/40 tracking-wide">
                    Elegant looks. Easy reservations.
                </div>

            </div>


            <!-- RIGHT SIDE -->
            <div class="bg-white p-8 md:p-12 flex flex-col justify-center">

                <!-- Header -->
                <div class="mb-8">

                    <span
                        class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.2em] uppercase text-[#B76E79]">
                        <span class="text-[#C9A46A]">✦</span>
                        Account Access
                    </span>

                    <h2 class="font-display text-4xl text-[#2E2A26] mt-3">
                        Sign in
                    </h2>

                    <p class="text-sm text-[#6F6662] mt-2">
                        Enter your account details to continue.
                    </p>

                </div>


                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />


                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">

                    @csrf


                    <!-- Email -->
                    <div>

                        <label for="email" class="block text-sm font-semibold text-[#5C1A2B] mb-2">
                            Email address
                        </label>

                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            autocomplete="username" placeholder="you@example.com"
                            class="w-full rounded-xl border border-[#E8D7DD] bg-[#FCF8FA] px-4 py-3.5 text-sm text-[#2E2A26] placeholder-[#A99CA0] outline-none transition focus:border-[#B76E79] focus:ring-2 focus:ring-[#B76E79]/10">

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />

                    </div>


                    <!-- Password -->
                    <div>

                        <div class="flex items-center justify-between mb-2">

                            <label for="password" class="text-sm font-semibold text-[#5C1A2B]">
                                Password
                            </label>

                            @if (Route::has('password.request'))

                                <a href="{{ route('password.request') }}"
                                    class="text-xs font-medium text-[#B76E79] hover:text-[#5C1A2B] transition">
                                    Forgot password?
                                </a>

                            @endif

                        </div>


                        <div class="relative">

                            <input id="password" type="password" name="password" required
                                autocomplete="current-password" placeholder="Enter your password"
                                class="w-full rounded-xl border border-[#E8D7DD] bg-[#FCF8FA] px-4 py-3.5 pr-12 text-sm text-[#2E2A26] placeholder-[#A99CA0] outline-none transition focus:border-[#B76E79] focus:ring-2 focus:ring-[#B76E79]/10">

                            <button type="button"
                                onclick="const p=document.getElementById('password'); p.type=p.type==='password'?'text':'password'"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[#B76E79] hover:text-[#5C1A2B] transition"
                                aria-label="Show or hide password">
                                ◉
                            </button>

                        </div>


                        <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    </div>


                    <!-- Remember Me -->
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-[#6F6662]">

                        <input type="checkbox" name="remember"
                            class="rounded border-[#D8C5CC] text-[#5C1A2B] focus:ring-[#B76E79]">

                        <span>
                            Remember me
                        </span>

                    </label>


                    <!-- Submit -->
                    <button type="submit"
                        class="luxury-button w-full flex items-center justify-center gap-3 rounded-xl bg-[#5C1A2B] px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#5C1A2B]/10">
                        <span>Sign in</span>
                        <span class="text-lg">→</span>
                    </button>

                </form>


                <!-- Register -->
                @if (Route::has('register'))

                    <div class="text-center mt-7 text-sm text-[#6F6662]">

                        New to Shyra Beautique?

                        <a href="{{ route('register') }}"
                            class="font-semibold text-[#5C1A2B] hover:text-[#B76E79] transition">
                            Create an account
                        </a>

                    </div>

                @endif


                <!-- Account Types -->
                <div class="mt-8 pt-6 border-t border-[#F0E4E8]">

                    <p class="text-center text-[10px] font-semibold tracking-[0.2em] uppercase text-[#A99CA0] mb-3">
                        Account Types
                    </p>

                    <div class="flex justify-center gap-2 flex-wrap">

                        <span class="px-3 py-1.5 rounded-full bg-[#F8EEF2] text-[#5C1A2B] text-xs font-medium">
                            Owner
                        </span>

                        <span class="px-3 py-1.5 rounded-full bg-[#F8EEF2] text-[#5C1A2B] text-xs font-medium">
                            Employee
                        </span>

                        <span class="px-3 py-1.5 rounded-full bg-[#F8EEF2] text-[#5C1A2B] text-xs font-medium">
                            Customer
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-guest-layout>