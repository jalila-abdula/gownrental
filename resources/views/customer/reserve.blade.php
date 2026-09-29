<x-app-layout>
    <div class="sb-page sb-reserve-page">
        <div class="sb-wrap">
            <div class="sb-heading">
                <div><span class="sb-kicker">RESERVATION REQUEST</span>
                    <h1>Reserve a <em>gown.</em></h1>
                    <p>Five quick steps. Nothing is submitted until you confirm at the end.</p>
                </div><a class="sb-outline-btn" href="{{ route('customer.catalog') }}">Back to collection</a>
            </div>
            @if($errors->any())
            <div class="sb-form-errors">{{ $errors->first() }}</div>@endif
            @if($lateFeePerDay <= 0)
                <div class="sb-form-errors">The shop needs to configure its daily late fee before accepting reservations.
            </div>@endif

            <div class="sb-reserve-layout">
                <aside class="sb-reserve-summary">
                    <a class="sb-reserve-summary-photo @if(!$gown->image) is-empty @endif" @if($gown->image)
                    style="background-image:url('{{ asset('storage/' . $gown->image) }}')" @endif
                        href="{{ route('customer.gowns.show', $gown) }}" aria-label="{{ $gown->name }}"></a>
                    <div class="sb-reserve-summary-body">
                        <span class="sb-kicker">YOUR GOWN</span>
                        <h2>{{ $gown->name }}</h2>
                        <p>{{ $gown->category->name ?? 'The Collection' }} · Size {{ $gown->size ?? 'Various' }}</p>
                        <div class="sb-reserve-summary-rows">
                            <div><span>Gown code</span><b>{{ $gown->gown_code }}</b></div>
                            <div><span>Color</span><b>{{ $gown->color ?? '—' }}</b></div>
                            <div><span>Daily late fee</span><b>₱{{ number_format($lateFeePerDay, 2) }}</b></div>
                            <div><span>Max rental</span><b>{{ $maxRentalDays }} days</b></div>
                        </div>
                        <div class="sb-reserve-summary-total"><span>Amount due
                                today</span><b>₱{{ number_format((float) $gown->rental_price + $securityDeposit, 2) }}</b>
                        </div>
                        <div class="sb-reserve-summary-note">The security deposit is held as collateral, not as rental
                            income. After return and inspection it is refunded, partially deducted for charges, or fully
                            deducted if the replacement value exceeds it.</div>
                    </div>
                </aside>

                <div class="sb-reserve-main">
                    <form method="POST" action="{{ route('customer.reserve.store', $gown) }}"
                        enctype="multipart/form-data" class="sb-reservation-form" data-reservation-wizard>@csrf

                        <div class="sb-progress-head">
                            <div class="sb-progress-meta">
                                <b data-progress-label>Step 1 of 5 · Customer &amp; dates</b>
                                <span data-progress-percent>20% complete</span>
                            </div>
                            <div class="sb-progress-track" role="progressbar" aria-valuemin="1" aria-valuemax="5"
                                aria-valuenow="1">
                                <div class="sb-progress-fill" data-progress-fill style="width:20%"></div>
                            </div>
                        </div>

                        <ol class="sb-step-dots" aria-label="Reservation steps">
                            <li data-step-dot class="is-active"><span>1</span><b>Dates</b></li>
                            <li data-step-dot><span>2</span><b>Gown &amp; sizing</b></li>
                            <li data-step-dot><span>3</span><b>Agreement</b></li>
                            <li data-step-dot><span>4</span><b>Payment</b></li>
                            <li data-step-dot><span>5</span><b>Confirmation</b></li>
                        </ol>

                        <div class="sb-reserve-card">

                            {{-- STEP 1 · Customer & dates --}}
                            <section class="sb-flow-step"><span class="sb-kicker">STEP 1</span>
                                <h2>Customer &amp; dates</h2>
                                <label>Booked by<input value="{{ auth()->user()->name }} · {{ auth()->user()->email }}"
                                        disabled></label>
                                <label>Contact number<input name="contact_number" required
                                        value="{{ old('contact_number') }}"
                                        placeholder="09XX XXX XXXX">@error('contact_number')<small>{{ $message }}</small>@enderror</label>
                                <label>Occasion<select name="event_type" required>
                                        <option value="">Choose your occasion</option>
                                        @foreach(['Wedding', 'Prom', 'Gala', 'Debut', 'Other special event'] as $occasion)
                                            <option @selected(old('event_type') === $occasion)>{{ $occasion }}</option>
                                        @endforeach
                                    </select>@error('event_type')<small>{{ $message }}</small>@enderror</label>
                                <div class="sb-form-row">
                                    <label>Pickup date<input type="date" name="pickup_date" required
                                            min="{{ today()->format('Y-m-d') }}" value="{{ old('pickup_date') }}"
                                            data-pickup>@error('pickup_date')<small>{{ $message }}</small>@enderror</label>
                                    <label>Return date<input type="date" name="return_date" required
                                            min="{{ today()->format('Y-m-d') }}" value="{{ old('return_date') }}"
                                            data-return>@error('return_date')<small>{{ $message }}</small>@enderror</label>
                                </div>
                                <label>Event date<input type="date" name="event_date"
                                        min="{{ today()->format('Y-m-d') }}" value="{{ old('event_date') }}"
                                        data-event-date>@error('event_date')<small>{{ $message }}</small>@enderror</label>

                                <div class="sb-availability" data-availability data-state="idle" aria-live="polite">
                                    <span class="sb-availability-icon" data-availability-icon>…</span>
                                    <div><b data-availability-title>Checking availability</b>
                                        <p data-availability-text>Pick your pickup and return dates to confirm this gown
                                            is free.</p>
                                    </div>
                                </div>

                                <p class="sb-form-intro">Rental is up to {{ $maxRentalDays }} days from pickup, then the
                                    gown is held for {{ $cleaningDays }} more days of professional cleaning. Late
                                    returns accrue ₱{{ number_format($lateFeePerDay, 2) }} per day.</p>
                            </section>

                            {{-- STEP 2 · Gown & sizing --}}
                            <section class="sb-flow-step"><span class="sb-kicker">STEP 2</span>
                                <h2>Gown &amp; sizing</h2>
                                <div class="sb-selected-gown"><b>{{ $gown->name }}</b><small>{{ $gown->gown_code }} ·
                                        {{ $gown->size ?? 'Various sizes' }}</small><strong>₱{{ number_format($gown->rental_price, 2) }}</strong>
                                </div>
                                <p class="sb-form-intro">Measurements in centimeters. Enter the ones you know so our
                                    stylists can prepare the fit in advance.</p>
                                <div class="sb-form-row">
                                    <label>Bust (cm)<input name="bust" type="number" min="0" max="300" step="0.1"
                                            value="{{ old('bust') }}" data-measure="bust"></label>
                                    <label>Waist (cm)<input name="waist" type="number" min="0" max="300" step="0.1"
                                            value="{{ old('waist') }}" data-measure="waist"></label>
                                </div>
                                <div class="sb-form-row">
                                    <label>Hips (cm)<input name="hips" type="number" min="0" max="300" step="0.1"
                                            value="{{ old('hips') }}" data-measure="hips"></label>
                                    <label>Height (cm)<input name="height" type="number" min="0" max="250" step="0.1"
                                            value="{{ old('height') }}" data-measure="height"></label>
                                </div>
                                <label>Gown length (cm)<input name="length" type="number" min="0" max="400" step="0.1"
                                        value="{{ old('length') }}" data-measure="length"></label>

                                <div class="sb-size-recommendation" data-size-rec data-state="idle">
                                    <span class="sb-availability-icon">?</span>
                                    <div><b data-size-title>Recommended size</b>
                                        <p data-size-text>Enter your bust, waist and hips to get a size recommendation.
                                        </p>
                                    </div>
                                </div>

                                <label>Notes for the stylist<textarea name="notes" rows="2"
                                        placeholder="Shoes, hairstyle, or fit preferences...">{{ old('notes') }}</textarea></label>
                            </section>

                            {{-- STEP 3 · Agreement --}}
                            <section class="sb-flow-step"><span class="sb-kicker">STEP 3</span>
                                <h2>Rental agreement</h2>
                                <label>Photo of valid government ID<span class="sb-file-field" data-file-field>
                                        <input type="file" name="government_id" accept="image/jpeg,image/png,image/webp"
                                            required data-file-input>
                                        <span class="sb-file-drop" data-file-drop>
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path
                                                    d="M4 16.5V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2.5M12 4v11m0-11 4 4m-4-4-4 4" />
                                            </svg>
                                            <b>Choose a photo</b>
                                            <small>JPEG, PNG or WebP · up to 5 MB</small>
                                        </span>
                                        <span class="sb-file-preview" data-file-preview hidden>
                                            <img alt="Selected government ID preview" data-file-image>
                                            <span class="sb-file-meta"><b data-file-name></b><small
                                                    data-file-size></small></span>
                                            <button type="button" class="sb-file-clear" data-file-clear
                                                aria-label="Remove selected file">✕</button>
                                        </span>
                                    </span>@error('government_id')<small>{{ $message }}</small>@enderror<small>Stored
                                        privately for verification only. The original physical ID is surrendered at
                                        pickup.</small></label>

                                <details class="sb-agreement" open>
                                    <summary><b>Rental agreement</b><span>Version {{ $agreementVersion }}</span>
                                    </summary>
                                    <ol>
                                        <li><b>Rental duration.</b> The gown may be rented for up to
                                            {{ $maxRentalDays }} days from the pickup date. The rental period ends at
                                            the agreed return date.
                                        </li>
                                        <li><b>Pickup requirements.</b> The renter must appear in person to collect the
                                            gown and surrender the original, physical, valid government ID. The ID is
                                            stored in a secure safe at the shop.</li>
                                        <li><b>Return deadline.</b> The gown must be returned by the agreed return date
                                            during business hours. A late fee of ₱{{ number_format($lateFeePerDay, 2) }}
                                            accrues for every late day.</li>
                                        <li><b>Damage fees.</b> The renter is financially responsible for the full
                                            repair cost of any damage, stain, or alteration caused during the rental
                                            period.</li>
                                        <li><b>Lost gown policy.</b> If the gown is lost or damaged beyond repair, the
                                            renter is liable for its full replacement value.</li>
                                        <li><b>Security deposit.</b> A deposit of
                                            ₱{{ number_format($securityDeposit, 2) }} is held for this gown. It is not
                                            rental income. After return and inspection it is refunded, partially
                                            deducted for charges, or fully deducted if the replacement value exceeds it.
                                        </li>
                                        <li><b>Cancellation policy.</b> Requests can be cancelled free of charge before
                                            staff approval. Any down payment becomes non-refundable once the reservation
                                            is confirmed.</li>
                                        <li><b>Cleaning policy.</b> Do not wash, dry-clean, steam, or alter the gown.
                                            The shop handles all professional cleaning. The gown remains unavailable for
                                            {{ $cleaningDays }} days after return for cleaning.
                                        </li>
                                    </ol>
                                </details>

                                <div class="sb-agreement-checks">
                                    <label class="sb-agreement-check"><input type="checkbox" name="agreement_accepted"
                                            value="1" required @checked(old('agreement_accepted'))><span>I have read and
                                            agree to the rental agreement and the terms above.</span></label>
                                    @error('agreement_accepted')<small
                                    class="sb-check-error">{{ $message }}</small>@enderror
                                    <label class="sb-agreement-check"><input type="checkbox" name="agreement_penalties"
                                            value="1" required @checked(old('agreement_penalties'))><span>I understand
                                            the penalties for late return, damage, and loss of the gown.</span></label>
                                    @error('agreement_penalties')<small
                                    class="sb-check-error">{{ $message }}</small>@enderror
                                    <label class="sb-agreement-check"><input type="checkbox" name="agreement_deposit"
                                            value="1" required @checked(old('agreement_deposit'))><span>I understand the
                                            payment and security deposit terms, including how deductions and refunds are
                                            handled.</span></label>
                                    @error('agreement_deposit')<small
                                    class="sb-check-error">{{ $message }}</small>@enderror
                                </div>
                            </section>

                            {{-- STEP 4 · Payment --}}
                            <section class="sb-flow-step"><span class="sb-kicker">STEP 4</span>
                                <h2>Payment</h2>
                                <div class="sb-payment-breakdown" data-payment-summary>
                                    <h3>Payment summary</h3>
                                    <div><span>Rental fee ·
                                            {{ $gown->name }}</span><b>₱{{ number_format($gown->rental_price, 2) }}</b>
                                    </div>
                                    <div><span>Security deposit <em>(refundable
                                                collateral)</em></span><b>₱{{ number_format($securityDeposit, 2) }}</b>
                                    </div>
                                    <div>
                                        <span>Total</span><b>₱{{ number_format((float) $gown->rental_price + $securityDeposit, 2) }}</b>
                                    </div>
                                </div>

                                <div class="sb-method-options" role="radiogroup" aria-label="Payment method">
                                    <label class="sb-method-option"><input type="radio" name="payment_method"
                                            value="gcash" data-method @checked(old('payment_method', 'gcash') === 'gcash')><span><b>GCash</b><small>Send via GCash, upload the
                                                receipt</small></span></label>
                                    <label class="sb-method-option"><input type="radio" name="payment_method"
                                            value="bank_transfer" data-method
                                            @checked(old('payment_method') === 'bank_transfer')><span><b>Bank
                                                transfer</b><small>Upload the transfer
                                                confirmation</small></span></label>
                                    <label class="sb-method-option"><input type="radio" name="payment_method"
                                            value="cash" data-method
                                            @checked(old('payment_method') === 'cash')><span><b>Cash</b><small>Settle at
                                                the counter during pickup</small></span></label>
                                </div>
                                @error('payment_method')<small class="sb-check-error">{{ $message }}</small>@enderror

                                <label>Reference number<input name="payment_reference_number"
                                        value="{{ old('payment_reference_number') }}"
                                        placeholder="GCash / transfer reference no."
                                        data-reference-number>@error('payment_reference_number')<small>{{ $message }}</small>@enderror</label>
                                <label data-proof-label>Payment proof<span class="sb-file-field" data-file-field>
                                        <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp"
                                            data-file-input data-payment-proof>
                                        <span class="sb-file-drop" data-file-drop>
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path
                                                    d="M4 16.5V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2.5M12 4v11m0-11 4 4m-4-4-4 4" />
                                            </svg>
                                            <b>Choose a receipt</b>
                                            <small data-file-accept>JPEG, PNG or WebP · up to 5 MB</small>
                                        </span>
                                        <span class="sb-file-preview" data-file-preview hidden>
                                            <img alt="Selected payment proof preview" data-file-image>
                                            <span class="sb-file-meta"><b data-file-name></b><small
                                                    data-file-size></small></span>
                                            <button type="button" class="sb-file-clear" data-file-clear
                                                aria-label="Remove selected file">✕</button>
                                        </span>
                                    </span>@error('payment_proof')<small>{{ $message }}</small>@enderror<small
                                        data-proof-hint>Upload a screenshot or receipt. Staff verify it before your
                                        reservation is confirmed.</small></label>
                                <label>Amount paid now (PHP)<input type="number" name="payment_amount" min="0.01"
                                        max="{{ (float) $gown->rental_price + $securityDeposit }}" step="0.01"
                                        value="{{ old('payment_amount', (float) $gown->rental_price + $securityDeposit) }}"
                                        required
                                        data-payment-amount>@error('payment_amount')<small>{{ $message }}</small>@enderror<small>Pay
                                        the full total or a non-refundable down payment. Any down payment becomes
                                        non-refundable once the reservation is confirmed.</small></label>
                                <p class="sb-form-intro">Remaining balance: <b data-balance-remaining>₱0.00</b> due at
                                    pickup.</p>
                            </section>

                            {{-- STEP 5 · Confirmation --}}
                            <section class="sb-flow-step"><span class="sb-kicker">STEP 5</span>
                                <h2>Confirmation</h2>
                                <p class="sb-form-intro">Check everything below. Nothing is submitted until you press
                                    the final button.</p>
                                <div class="sb-confirm-list">
                                    <div><span>Gown</span><b>{{ $gown->name }} · {{ $gown->gown_code }}</b></div>
                                    <div><span>Event</span><b data-confirm="event_date">—</b></div>
                                    <div><span>Pickup</span><b data-confirm="pickup_date">—</b></div>
                                    <div><span>Return</span><b data-confirm="return_date">—</b></div>
                                    <div><span>Payment method</span><b data-confirm="payment_method">—</b></div>
                                    <div><span>Amount paid now</span><b data-confirm="payment_amount">—</b></div>
                                    <div>
                                        <span>Total</span><b>₱{{ number_format((float) $gown->rental_price + $securityDeposit, 2) }}</b>
                                    </div>
                                    <div><span>Security deposit</span><b>₱{{ number_format($securityDeposit, 2) }}
                                            (refundable)</b></div>
                                    <div><span>Agreement</span><b>Version {{ $agreementVersion }} · accepted on
                                            submit</b></div>
                                </div>
                                <p class="sb-confirm-status"><span class="sb-status-dot is-pending"></span> Once
                                    submitted, your reservation is <b>Pending Approval</b> until staff verify your
                                    agreement and payment.</p>
                            </section>

                            <div class="sb-step-controls">
                                <button type="button" class="sb-outline-btn" data-step-back>Back</button>
                                <span data-step-count>Step 1 of 5</span>
                                <button type="button" class="sb-btn" data-step-next>Continue</button>
                                <button class="sb-btn sb-btn-confirm" type="submit" data-step-submit hidden>Submit
                                    Reservation Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-reservation-wizard]');
            const steps = [...form.querySelectorAll('.sb-flow-step')];
            const dots = [...form.querySelectorAll('[data-step-dot]')];
            const fill = form.querySelector('[data-progress-fill]');
            const track = form.querySelector('.sb-progress-track');
            const labels = ['Customer & dates', 'Gown & sizing', 'Agreement', 'Payment', 'Confirmation'];

            const errors = @json(array_keys($errors->getMessages()));
            let current = steps.findIndex(step => errors.some(name => step.querySelector(`[name="${name}"]`)));
            if (current < 0) current = 0;

            const peso = n => '\u20b1' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const formatDate = iso => iso ? new Date(iso + 'T00:00:00').toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' }) : '\u2014';

            /* ---- Step 1: live availability so the customer cannot continue on blocked dates ---- */
            const pickup = form.querySelector('[data-pickup]');
            const ret = form.querySelector('[data-return]');
            const event = form.querySelector('[data-event-date]');
            const availabilityBox = form.querySelector('[data-availability]');
            const maxRentalDays = @json($maxRentalDays);
            const availabilityUrl = @json(route('customer.reserve.availability', $gown));
            let datesVerified = false;

            const setAvailability = (state, title, text) => {
                availabilityBox.dataset.state = state;
                form.querySelector('[data-availability-icon]').textContent = state === 'ok' ? '\u2713' : (state === 'error' ? '!' : '\u2026');
                form.querySelector('[data-availability-title]').textContent = title;
                form.querySelector('[data-availability-text]').textContent = text;
            };

            const checkAvailability = async () => {
                if (!pickup.value || !ret.value) {
                    datesVerified = false;
                    setAvailability('idle', 'Checking availability', 'Pick your pickup and return dates to confirm this gown is free.');
                    return;
                }
                datesVerified = false;
                setAvailability('busy', 'Checking availability', 'Verifying this gown is free for your dates\u2026');
                try {
                    const query = new URLSearchParams({ pickup_date: pickup.value, return_date: ret.value });
                    const response = await fetch(availabilityUrl + '?' + query, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('bad response');
                    const result = await response.json();
                    if (result.available) {
                        datesVerified = true;
                        setAvailability('ok', 'Available for your selected dates', result.summary + ' \u00b7 ' + result.rental_days + ' day rental \u00b7 cleaning until ' + result.cleaning_until);
                    } else {
                        setAvailability('busy', 'Not available for these dates', result.reason);
                    }
                } catch (error) {
                    setAvailability('error', 'Could not verify availability', 'Please try again, or contact the boutique to book this gown.');
                }
            };

            const applyReturnWindow = () => {
                if (!pickup.value) return;
                const parts = pickup.value.split('-').map(Number);
                const max = new Date(Date.UTC(parts[0], parts[1] - 1, parts[2] + maxRentalDays)).toISOString().slice(0, 10);
                ret.min = pickup.value;
                ret.max = max;
                event.min = pickup.value;
                event.max = ret.value || max;
                if (ret.value && (ret.value < pickup.value || ret.value > max)) ret.value = '';
            };

            pickup.addEventListener('change', () => { applyReturnWindow(); checkAvailability(); });
            ret.addEventListener('change', () => {
                if (pickup.value && ret.value && ret.value < pickup.value) ret.value = pickup.value;
                event.max = ret.value || ret.max;
                checkAvailability();
            });
            applyReturnWindow();
            if (pickup.value && ret.value) checkAvailability();

            /* ---- Step 2: size recommendation ---- */
            const recBox = form.querySelector('[data-size-rec]');
            const recTitle = recBox.querySelector('[data-size-title]');
            const recText = recBox.querySelector('[data-size-text]');

            const recommend = () => {
                const entered = ['bust', 'waist', 'hips']
                    .map(key => [key, parseFloat(form.querySelector('[data-measure="' + key + '"]').value)])
                    .filter(entry => !isNaN(entry[1]));
                if (!entered.length) {
                    recBox.dataset.state = 'idle';
                    recTitle.textContent = 'Recommended size';
                    recText.textContent = 'Enter your bust, waist and hips to get a size recommendation.';
                    return;
                }
                recBox.dataset.state = 'ok';
                recTitle.textContent = 'Recommended size: ' + (@json($gown->size) || 'ask the boutique');
                recText.textContent = 'Based on ' + entered.map(entry => entry[0] + ' ' + entry[1] + ' cm').join(', ')
                    + '. Our stylists confirm the fit at pickup and can pin the gown for you.';
            };
            form.querySelectorAll('[data-measure]').forEach(input => input.addEventListener('input', recommend));
            recommend();

            /* ---- Step 4: payment method behaviour ---- */
            const methodInputs = [...form.querySelectorAll('[data-method]')];
            const proofInput = form.querySelector('[data-payment-proof]');
            const proofLabel = form.querySelector('[data-proof-label]');
            const proofHint = form.querySelector('[data-proof-hint]');
            const referenceInput = form.querySelector('[data-reference-number]');
            const amountInput = form.querySelector('[data-payment-amount]');
            const total = {{ (float) $gown->rental_price + $securityDeposit }};

            const currentMethod = () => (methodInputs.find(input => input.checked) || {}).value || 'gcash';

            const updateBalance = () => {
                const paid = parseFloat(amountInput.value) || 0;
                form.querySelector('[data-balance-remaining]').textContent = peso(Math.max(0, total - paid));
            };

            const applyMethod = () => {
                const method = currentMethod();
                const isCash = method === 'cash';
                methodInputs.forEach(input => input.closest('.sb-method-option').classList.toggle('is-selected', input.checked));
                proofInput.required = !isCash;
                proofLabel.hidden = isCash;
                referenceInput.required = method === 'gcash';
                referenceInput.disabled = isCash;
                // Kept enabled so a token still validates: cash books a 0.01 marker that
                // staff settle at the counter, and the real amount is never trusted.
                amountInput.readOnly = isCash;
                if (isCash) {
                    amountInput.value = '0.01';
                    proofHint.textContent = '';
                } else {
                    amountInput.value = Math.max(0.01, parseFloat(amountInput.value) || total);
                    proofHint.textContent = method === 'gcash'
                        ? 'Upload a GCash screenshot or receipt. Staff verify it before your reservation is confirmed.'
                        : 'Upload the bank transfer confirmation. Staff verify it before your reservation is confirmed.';
                }
                updateBalance();
            };

            amountInput.addEventListener('input', updateBalance);
            methodInputs.forEach(input => input.addEventListener('change', applyMethod));
            applyMethod();

            /* ---- Step 5: live confirmation summary ---- */
            const confirmField = name => form.querySelector('[data-confirm="' + name + '"]');
            const refreshConfirm = () => {
                confirmField('event_date').textContent = formatDate(event.value);
                confirmField('pickup_date').textContent = formatDate(pickup.value);
                confirmField('return_date').textContent = formatDate(ret.value);
                const method = currentMethod();
                confirmField('payment_method').textContent = method === 'bank_transfer' ? 'Bank transfer' : method.charAt(0).toUpperCase() + method.slice(1);
                confirmField('payment_amount').textContent = method === 'cash' ? 'Settled at pickup' : peso(amountInput.value);
            };
            [event, pickup, ret, amountInput].forEach(input => input.addEventListener('change', refreshConfirm));
            methodInputs.forEach(input => input.addEventListener('change', refreshConfirm));
            refreshConfirm();

            /* ---- File pickers: show the chosen image before submitting ---- */
            const formatSize = bytes => bytes < 1024 * 1024
                ? Math.max(1, Math.round(bytes / 1024)) + ' KB'
                : (bytes / (1024 * 1024)).toFixed(1) + ' MB';

            [...form.querySelectorAll('[data-file-field]')].forEach(field => {
                const input = field.querySelector('[data-file-input]');
                const drop = field.querySelector('[data-file-drop]');
                const preview = field.querySelector('[data-file-preview]');
                const image = field.querySelector('[data-file-image]');
                const nameOut = field.querySelector('[data-file-name]');
                const sizeOut = field.querySelector('[data-file-size]');
                const clear = field.querySelector('[data-file-clear]');
                let objectUrl = null;

                const reset = () => {
                    if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
                    input.value = '';
                    image.removeAttribute('src');
                    preview.hidden = true;
                    drop.hidden = false;
                    field.dataset.state = 'empty';
                };

                input.addEventListener('change', () => {
                    const file = input.files && input.files[0];
                    if (!file) { reset(); return; }
                    if (!file.type.startsWith('image/')) {
                        nameOut.textContent = 'That file is not an image';
                        sizeOut.textContent = 'Please choose a JPEG, PNG, or WebP file.';
                        image.removeAttribute('src');
                        preview.hidden = false;
                        drop.hidden = true;
                        field.dataset.state = 'error';
                        input.value = '';
                        return;
                    }
                    if (objectUrl) URL.revokeObjectURL(objectUrl);
                    objectUrl = URL.createObjectURL(file);
                    image.src = objectUrl;
                    nameOut.textContent = file.name;
                    sizeOut.textContent = formatSize(file.size);
                    preview.hidden = false;
                    drop.hidden = true;
                    field.dataset.state = 'ready';
                });

                clear.addEventListener('click', event => {
                    // The whole field is a <label>, so the click would otherwise reopen
                    // the picker right after clearing it.
                    event.preventDefault();
                    event.stopPropagation();
                    reset();
                });

                field.addEventListener('dragover', event => { event.preventDefault(); field.dataset.state = 'ready'; });
                field.addEventListener('dragleave', () => { field.dataset.state = input.files.length ? 'ready' : 'empty'; });
                field.addEventListener('drop', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    if (event.dataTransfer.files.length) {
                        input.files = event.dataTransfer.files;
                        input.dispatchEvent(new Event('change'));
                    }
                });
            });

            /* ---- Wizard shell ---- */
            const scrollTop = () => form.querySelector('.sb-progress-head').scrollIntoView({ behavior: 'smooth', block: 'start' });

            const show = () => {
                steps.forEach((step, index) => step.hidden = index !== current);
                dots.forEach((dot, index) => {
                    dot.classList.toggle('is-active', index === current);
                    dot.classList.toggle('is-complete', index < current);
                    // Completed steps read as a check, not a dark filled circle.
                    dot.querySelector('span').innerHTML = index < current ? '\u2713' : String(index + 1);
                });
                const percent = Math.round(((current + 1) / steps.length) * 100);
                fill.style.width = percent + '%';
                track.setAttribute('aria-valuenow', current + 1);
                form.querySelector('[data-progress-percent]').textContent = percent + '% complete';
                form.querySelector('[data-progress-label]').textContent = 'Step ' + (current + 1) + ' of ' + steps.length + ' \u00b7 ' + labels[current];
                form.querySelector('[data-step-count]').textContent = 'Step ' + (current + 1) + ' of ' + steps.length;
                form.querySelector('[data-step-back]').disabled = current === 0;
                form.querySelector('[data-step-next]').hidden = current === steps.length - 1;
                form.querySelector('[data-step-submit]').hidden = current !== steps.length - 1;
            };

            const stepIsValid = index => {
                if (index === 0 && !datesVerified) {
                    setAvailability('busy', 'Confirm your dates first', 'This gown must be verified as available before you can continue.');
                    if (pickup.value && ret.value) checkAvailability();
                    else pickup.focus();
                    return false;
                }
                const invalid = [...steps[index].querySelectorAll('input, select, textarea')]
                    .find(input => !input.disabled && !input.checkValidity());
                if (invalid) { invalid.reportValidity(); return false; }
                return true;
            };

            form.querySelector('[data-step-back]').addEventListener('click', () => { if (current > 0) { current--; show(); scrollTop(); } });
            form.querySelector('[data-step-next]').addEventListener('click', () => {
                if (!stepIsValid(current)) return;
                if (current < steps.length - 1) { current++; show(); scrollTop(); }
            });
            form.addEventListener('submit', event => { if (!stepIsValid(current)) event.preventDefault(); });

            show();
        })();
    </script>
</x-app-layout>