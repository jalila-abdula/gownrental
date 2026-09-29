<x-app-layout>
    <div class="sb-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div>

                    <h1>
                        Boutique
                        <em>
                            settings.
                        </em>
                    </h1>

                    <p>
                        Keep the boutique details and rental policies up to date.
                    </p>
                </div>

            </div>
            @if(session('success'))
                <div class="sb-success">{{ session('success') }}

                </div>
            @endif

            <section class="sb-panel sb-settings-card">
                <form method="POST" action="{{ route('owner.settings.save') }}" class="sb-reservation-form">

                    @csrf

                    @method('PUT')
                    <h2>
                        Business details
                    </h2>

                    <p class="sb-form-intro">
                        These details help staff apply consistent rental policies.
                    </p>

                    <label>
                        Boutique name

                        <input name="shop_name"
                            value="{{ old('shop_name', $settings->get('shop_name', 'Shyra Beautique')) }}" required>
                    </label>

                    <div class="sb-form-row">
                        <label>

                            Contact email

                            <input type="email" name="contact_email"
                                value="{{ old('contact_email', $settings->get('contact_email')) }}">
                        </label>

                        <label>
                            Contact number<input name="contact_number"
                                value="{{ old('contact_number', $settings->get('contact_number')) }}">
                        </label>
                    </div>

                    <label>
                        Late return fee per day (₱)
                        <input type="number" min="0.01" step="0.01" name="late_fee_per_day"
                            value="{{ old('late_fee_per_day', $settings->get('late_fee_per_day', '0')) }}" required>
                    </label>

                    <small class="sb-settings-hint">

                        A late fee is added for every late day when staff record the return. Configure a positive daily rate before accepting new reservations.

                    </small>

                    <button class="sb-btn" type="submit">

                        Save boutique settings


                    </button>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
