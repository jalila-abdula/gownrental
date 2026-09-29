<x-app-layout>
<div class="sb-page"><div class="sb-wrap">
    <div class="sb-heading"><div><span class="sb-kicker">RESERVATION REQUEST</span><h1>Reserve a <em>gown.</em></h1><p>Complete each step to send your booking for staff confirmation.</p></div><a class="sb-outline-btn" href="{{ route('customer.catalog') }}">Back to collection</a></div>
    @if($errors->any())<div class="sb-form-errors">{{ $errors->first() }}</div>@endif
    @if($lateFeePerDay <= 0)<div class="sb-form-errors">The shop needs to configure its daily late fee before accepting reservations.</div>@endif
    <form method="POST" action="{{ route('customer.reserve.store', $gown) }}" enctype="multipart/form-data" class="sb-reservation-form sb-panel sb-customer-flow" data-reservation-wizard>@csrf
        <ol class="sb-step-progress" aria-label="Reservation steps"><li data-step-indicator><span>1</span><b>Customer &amp; dates</b></li><li data-step-indicator><span>2</span><b>Gown &amp; sizing</b></li><li data-step-indicator><span>3</span><b>ID, agreement &amp; payment</b></li></ol>
        <section class="sb-flow-step"><span class="sb-kicker">STEP 1</span><h2>Customer &amp; dates</h2>
            <label>Customer<input value="{{ auth()->user()->name }} · {{ auth()->user()->email }}" disabled></label>
            <label>Contact number<input name="contact_number" required value="{{ old('contact_number') }}" placeholder="09XX XXX XXXX">@error('contact_number')<small>{{ $message }}</small>@enderror</label>
            <div class="sb-form-row"><label>Pickup date<input id="pickup-date" type="date" name="pickup_date" required min="{{ today()->format('Y-m-d') }}" value="{{ old('pickup_date') }}">@error('pickup_date')<small>{{ $message }}</small>@enderror</label><label>Return date<input id="return-date" type="date" name="return_date" required min="{{ today()->format('Y-m-d') }}" value="{{ old('return_date') }}">@error('return_date')<small>{{ $message }}</small>@enderror</label></div>
            <p class="sb-form-intro">Rental period is up to 3 days from pickup. After return, the gown is reserved for 3 days of professional cleaning.</p>
        </section>
        <section class="sb-flow-step"><span class="sb-kicker">STEP 2</span><h2>Gown &amp; sizing</h2>
            <div class="sb-selected-gown"><b>{{ $gown->name }}</b><small>{{ $gown->gown_code }} · {{ $gown->size ?? 'Various sizes' }}</small><strong>Rental fee: ₱{{ number_format($gown->rental_price, 2) }}</strong></div>
            <p class="sb-form-intro">Measurements in centimeters; enter the measurements you know.</p>
            <div class="sb-form-row"><label>Bust<input name="bust" type="number" min="0" max="300" step="0.1" value="{{ old('bust') }}"></label><label>Waist<input name="waist" type="number" min="0" max="300" step="0.1" value="{{ old('waist') }}"></label></div>
            <div class="sb-form-row"><label>Hips<input name="hips" type="number" min="0" max="300" step="0.1" value="{{ old('hips') }}"></label><label>Length<input name="length" type="number" min="0" max="400" step="0.1" value="{{ old('length') }}"></label></div>
            <label>Occasion<select name="event_type" required><option value="">Choose your occasion</option>@foreach(['Wedding', 'Prom', 'Gala', 'Debut', 'Other special event'] as $occasion)<option @selected(old('event_type') === $occasion)>{{ $occasion }}</option>@endforeach</select></label>
        </section>
        <section class="sb-flow-step"><span class="sb-kicker">STEP 3</span><h2>ID collateral, agreement &amp; payment</h2>
            <label>Photo of valid government ID<input type="file" name="government_id" accept="image/jpeg,image/png,image/webp" required><small>Stored privately for reservation verification.</small></label>
            <div class="sb-agreement"><h3>Rental agreement</h3><ol><li>Renter surrenders the original, physical, valid government ID at pickup.</li><li>The shop stores the physical ID in a secure safe. No cash security deposit is charged.</li><li>Do not wash, dry-clean, or alter the gown. The shop handles professional cleaning.</li><li>A late fee of ₱{{ number_format($lateFeePerDay, 2) }} accrues for every late day. The ID is held until the gown is returned and all balances and late fees are paid.</li><li>Renter is financially responsible for repair costs or the full replacement value if the gown is ruined or lost.</li><li>The original ID is returned only after the gown is safely returned and all outstanding charges are cleared.</li></ol><label class="sb-agreement-check"><input type="checkbox" name="agreement_accepted" value="1" required @checked(old('agreement_accepted'))> I have read and agree to these rental terms.</label></div>
            <div class="sb-payment-breakdown"><h3>Payment breakdown</h3><div><span>Rental fee</span><b>₱{{ number_format($gown->rental_price, 2) }}</b></div><div><span>Security deposit</span><b>₱0.00</b></div><div><span>Total amount due</span><b>₱{{ number_format($gown->rental_price, 2) }}</b></div></div>
            <label>Pay now (PHP)<input type="number" name="payment_amount" min="0.01" max="{{ $gown->rental_price }}" step="0.01" value="{{ old('payment_amount', $gown->rental_price) }}" required><small>Pay the full rental fee or submit a non-refundable down payment. Your reservation is held while staff review the GCash receipt.</small></label>
            <label>GCash payment receipt<input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp" required></label>
            <label>Occasion notes<textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
        </section>
        <div class="sb-step-controls"><button type="button" class="sb-outline-btn" data-step-back>Back</button><span data-step-count>Step 1 of 3</span><button type="button" class="sb-btn" data-step-next>Next</button><button class="sb-btn" type="submit" data-step-submit hidden>Submit agreement and payment</button></div>
    </form>
</div></div>
<script>(() => {
    const form=document.querySelector('[data-reservation-wizard]'), steps=[...form.querySelectorAll('.sb-flow-step')], indicators=[...form.querySelectorAll('[data-step-indicator]')];
    const errors=@json(array_keys($errors->getMessages()));
    let current=steps.findIndex(step=>errors.some(name=>step.querySelector(`[name="${name}"]`)));
    if(current<0)current=0;
    const show=()=>{steps.forEach((step,index)=>step.hidden=index!==current);indicators.forEach((item,index)=>{item.classList.toggle('is-active',index===current);item.classList.toggle('is-complete',index<current);});form.querySelector('[data-step-count]').textContent=`Step ${current+1} of ${steps.length}`;form.querySelector('[data-step-back]').disabled=current===0;form.querySelector('[data-step-next]').hidden=current===steps.length-1;form.querySelector('[data-step-submit]').hidden=current!==steps.length-1;};
    form.querySelector('[data-step-back]').addEventListener('click',()=>{if(current>0){current--;show();}});
    form.querySelector('[data-step-next]').addEventListener('click',()=>{const invalid=[...steps[current].querySelectorAll('input,select,textarea')].find(field=>!field.checkValidity());if(invalid){invalid.reportValidity();return;}if(current<steps.length-1){current++;show();}});
    show();
    const pickup=document.getElementById('pickup-date'), returned=document.getElementById('return-date'); const update=()=>{if(!pickup.value)return;const [y,m,d]=pickup.value.split('-').map(Number);const max=new Date(Date.UTC(y,m-1,d+3)).toISOString().slice(0,10);returned.min=pickup.value;returned.max=max;if(returned.value&&(returned.value<pickup.value||returned.value>max))returned.value='';};pickup.addEventListener('change',update);update();
})();</script>
</x-app-layout>
