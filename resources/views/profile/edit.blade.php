<x-app-layout>
    <div class="sb-page sb-profile-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div>
                    <span class="sb-kicker">ACCOUNT SETTINGS</span>
                    <h1>Your <em>profile.</em></h1>
                    <p>Manage your personal information, password, and account.</p>
                </div>
            </div>

            <div class="sb-profile-stack">
                <section class="sb-panel sb-profile-panel">
                    @include('profile.partials.update-profile-information-form')
                </section>

                <section class="sb-panel sb-profile-panel">
                    @include('profile.partials.update-password-form')
                </section>

                <section class="sb-panel sb-profile-panel sb-profile-danger-panel">
                    @include('profile.partials.delete-user-form')
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
