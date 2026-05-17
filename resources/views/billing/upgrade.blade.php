@extends('layouts.app')

@section('title', 'Upgrade to Pro — ResumeCoach AI')

@push('styles')
<style>
    .upgrade-wrap { max-width:960px; margin:0 auto; padding:3rem 2rem; }
    .upgrade-hero { text-align:center; margin-bottom:3rem; }
    .upgrade-hero h1 { font-family:var(--serif); font-size:clamp(2rem,4vw,2.8rem); margin-bottom:.75rem; line-height:1.2; }
    .upgrade-hero h1 em { font-style:italic; color:var(--accent); }
    .upgrade-hero p { color:var(--muted); font-size:1rem; max-width:500px; margin:0 auto; line-height:1.7; }

    .plans-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2.5rem; }
    .plan-box { background:white; border:2px solid var(--border); border-radius:12px; padding:2rem; cursor:pointer; position:relative; transition:border-color .2s, box-shadow .2s; }
    .plan-box:hover { border-color:#ccc; }
    .plan-box.selected { border-color:var(--accent); box-shadow:0 0 0 3px rgba(200,67,42,.1); }
    .plan-box .best-badge { position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:var(--accent); color:white; font-size:.7rem; font-weight:700; padding:.22rem .85rem; border-radius:100px; white-space:nowrap; letter-spacing:.04em; text-transform:uppercase; }
    .plan-radio { display:none; }
    .plan-name { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); margin-bottom:.5rem; }
    .plan-price { font-family:var(--serif); font-size:2.8rem; line-height:1; margin-bottom:.2rem; }
    .plan-price sub { font-family:var(--sans); font-size:.85rem; color:var(--muted); }
    .plan-save { display:inline-block; font-size:.75rem; background:#f0faf4; color:var(--success); border-radius:100px; padding:.15rem .6rem; font-weight:600; margin:.4rem 0 .75rem; }
    .plan-features { list-style:none; border-top:1px solid var(--border); padding-top:.85rem; margin-top:.5rem; }
    .plan-features li { font-size:.855rem; padding:.2rem 0; display:flex; gap:.5rem; color:var(--ink); }
    .plan-features li::before { content:'✓'; color:var(--success); font-weight:700; flex-shrink:0; }

    .checkout-box { background:white; border:1px solid var(--border); border-radius:12px; padding:2rem; max-width:460px; margin:0 auto; }
    .checkout-box h3 { font-family:var(--serif); font-size:1.25rem; margin-bottom:.35rem; text-align:center; }
    .checkout-box p { color:var(--muted); font-size:.855rem; text-align:center; margin-bottom:1.25rem; line-height:1.6; }
    .summary-row { background:var(--cream); border-radius:6px; padding:.7rem 1rem; display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; font-size:.875rem; }
    .summary-row strong { font-weight:600; }
    .summary-row span { color:var(--muted); }
    .paypal-wrap { min-height:50px; }

    .trust-row { display:flex; justify-content:center; gap:2rem; flex-wrap:wrap; margin-top:2rem; padding-top:2rem; border-top:1px solid var(--border); }
    .trust-item { display:flex; align-items:center; gap:.4rem; font-size:.8rem; color:var(--muted); }

    @media(max-width:640px) { .plans-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="upgrade-wrap">

    @if(session('success'))
        <div class="alert alert-success" style="max-width:460px;margin:0 auto 2rem;">{{ session('success') }}</div>
    @endif

    @if($user->isPro())
        <div style="text-align:center;padding:4rem 2rem;">
            <div style="font-size:3rem;margin-bottom:1rem;">✦</div>
            <h1 style="font-family:var(--serif);font-size:2rem;margin-bottom:.75rem;">You're already on Pro!</h1>
            <p style="color:var(--muted);margin-bottom:2rem;">Enjoy unlimited resume and cover letter generations.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard →</a>
        </div>
    @else

        <div class="upgrade-hero">
            <h1>Unlimited resumes.<br><em>One honest price.</em></h1>
            <p>No dark-pattern billing. No surprise charges. Cancel anytime directly from your PayPal account.</p>
        </div>

        {{-- Plan selector --}}
        <div class="plans-grid">
            <div class="plan-box" id="plan-monthly" onclick="selectPlan('monthly')" role="button" tabindex="0">
                <div class="plan-name">Monthly</div>
                <div class="plan-price">$4<sub>/mo</sub></div>
                <ul class="plan-features">
                    <li>Unlimited resume generations</li>
                    <li>Unlimited cover letters</li>
                    <li>AI coaching conversations</li>
                    <li>ATS keyword optimisation</li>
                    <li>Cancel anytime</li>
                </ul>
            </div>

            <div class="plan-box selected" id="plan-annual" onclick="selectPlan('annual')" role="button" tabindex="0">
                <div class="best-badge">Best value — Save 17%</div>
                <div class="plan-name">Annual</div>
                <div class="plan-price">$40<sub>/yr</sub></div>
                <span class="plan-save">≈ $3.33/mo</span>
                <ul class="plan-features">
                    <li>Everything in Monthly</li>
                    <li>17% cheaper than monthly</li>
                    <li>Ideal for active job seekers</li>
                    <li>Priority support</li>
                    <li>Cancel anytime</li>
                </ul>
            </div>
        </div>

        {{-- Checkout box --}}
        <div class="checkout-box">
            <h3>Complete your upgrade</h3>
            <p>Secure recurring billing via PayPal. You'll confirm on PayPal's secure page.</p>

            <div class="summary-row">
                <strong id="summary-plan">Annual Plan</strong>
                <span id="summary-price">$40 / year</span>
            </div>

            {{-- PayPal buttons — one per plan, toggled by JS --}}
            <div class="paypal-wrap" id="paypal-annual-wrap">
                <div id="paypal-button-container-annual"></div>
            </div>

            <div class="paypal-wrap" id="paypal-monthly-wrap" style="display:none;">
                <div id="paypal-button-container-monthly"></div>
            </div>

            <div id="paypal-error" style="display:none;background:#fdf0ed;border:1px solid #f5ccc5;color:#c8432a;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;margin-top:1rem;"></div>
        </div>

        <div class="trust-row">
            <div class="trust-item">🔒 Secured by PayPal</div>
            <div class="trust-item">✓ Cancel anytime</div>
            <div class="trust-item">✓ No hidden fees</div>
            <div class="trust-item">✓ Instant access</div>
        </div>

    @endif
</div>
@endsection

@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id=BAAfetb95ri-dEEpyKl8vwa8XnwIA8_z2QnRvpha-L43-jd3xw2stsFxYb536bcUTN7usCxiBHI571XspI&vault=true&intent=subscription"
        data-sdk-integration-source="button-factory"></script>

<script>
const ACTIVATE_URL = "{{ route('billing.paypal.activate') }}";
const SUCCESS_URL  = "{{ route('billing.success') }}";
const CSRF         = document.querySelector('meta[name="csrf-token"]').content;

// ── Plan IDs ───────────────────────────────────────────────────
// Replace MONTHLY_PLAN_ID below with the plan ID you create in
// PayPal Dashboard → Subscriptions → Plans → Create Plan ($4/mo)
const PLAN_IDS = {
    annual:  'P-4S005922JB426663ANIEZ6TY',   // your existing annual plan
    monthly: 'P-6MY012515D384684UNIEMN6Y'
};

// ── Plan switcher ──────────────────────────────────────────────
let currentPlan = 'annual';

function selectPlan(plan) {
    currentPlan = plan;

    document.getElementById('plan-annual').classList.toggle('selected', plan === 'annual');
    document.getElementById('plan-monthly').classList.toggle('selected', plan === 'monthly');

    document.getElementById('paypal-annual-wrap').style.display  = plan === 'annual'  ? 'block' : 'none';
    document.getElementById('paypal-monthly-wrap').style.display = plan === 'monthly' ? 'block' : 'none';

    document.getElementById('summary-plan').textContent  = plan === 'annual' ? 'Annual Plan'  : 'Monthly Plan';
    document.getElementById('summary-price').textContent = plan === 'annual' ? '$40 / year'   : '$4 / month';
    document.getElementById('paypal-error').style.display = 'none';
}

// ── Shared onApprove handler ───────────────────────────────────
function handleApprove(data, plan) {
    fetch(ACTIVATE_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ subscription_id: data.subscriptionID, plan })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.href = SUCCESS_URL;
        } else {
            showError('Activation failed. Contact support with ID: ' + data.subscriptionID);
        }
    })
    .catch(() => showError('Network error. Contact support with ID: ' + data.subscriptionID));
}

function showError(msg) {
    const el = document.getElementById('paypal-error');
    el.textContent = msg;
    el.style.display = 'block';
}

const BUTTON_STYLE = {
    shape:  'rect',
    color:  'gold',
    layout: 'vertical',
    label:  'subscribe'
};

// ── Annual PayPal button ───────────────────────────────────────
paypal.Buttons({
    style: BUTTON_STYLE,
    createSubscription: (data, actions) =>
        actions.subscription.create({ plan_id: PLAN_IDS.annual }),
    onApprove: (data) => handleApprove(data, 'annual'),
    onError:   (err)  => { showError('PayPal error. Please try again.'); console.error(err); },
    onCancel:  ()     => {}
}).render('#paypal-button-container-annual');

// ── Monthly PayPal button ──────────────────────────────────────
paypal.Buttons({
    style: BUTTON_STYLE,
    createSubscription: (data, actions) =>
        actions.subscription.create({ plan_id: PLAN_IDS.monthly }),
    onApprove: (data) => handleApprove(data, 'monthly'),
    onError:   (err)  => { showError('PayPal error. Please try again.'); console.error(err); },
    onCancel:  ()     => {}
}).render('#paypal-button-container-monthly');

// Keyboard accessibility for plan cards
document.querySelectorAll('.plan-box').forEach(box => {
    box.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') box.click();
    });
});
</script>
@endpush