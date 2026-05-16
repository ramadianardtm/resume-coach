<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class BillingController extends BaseController
{
    private StripeClient $stripe;

    public function __construct()
    {
        // StripeClient is the modern, IDE-friendly way — no static Stripe::setApiKey needed
        $this->stripe = new StripeClient((string) config('services.stripe.secret'));
    }

    // ── Upgrade page ───────────────────────────────────────────

    public function upgrade(): \Illuminate\View\View
    {
        /** @var User $user */
        $user = Auth::user();
        return view('billing.upgrade', compact('user'));
    }

    // ── Create Stripe Checkout Session ────────────────────────

    public function checkout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'plan' => ['required', 'in:pro_monthly,pro_annual'],
        ]);

        /** @var User $user */
        $user    = Auth::user();
        $priceId = $request->plan === 'pro_annual'
            ? (string) config('services.stripe.price_annual')
            : (string) config('services.stripe.price_monthly');

        $checkout = $this->stripe->checkout->sessions->create([
            'customer_email'       => $user->email,
            'payment_method_types' => ['card'],
            'line_items'           => [['price' => $priceId, 'quantity' => 1]],
            'mode'                 => 'subscription',
            'success_url'          => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => route('billing.upgrade'),
            'metadata'             => ['user_id' => (string) $user->id, 'plan' => $request->plan],
        ]);

        return redirect((string) $checkout->url);
    }

    // ── Stripe Webhook ─────────────────────────────────────────

    public function webhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $secret    = (string) config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature mismatch');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Use if/else instead of match() so IDEs don't flag exhaustiveness
        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutComplete($event->data->object);
        } elseif ($event->type === 'customer.subscription.updated') {
            $this->handleSubscriptionUpdated($event->data->object);
        } elseif ($event->type === 'customer.subscription.deleted') {
            $this->handleSubscriptionDeleted($event->data->object);
        } elseif ($event->type === 'invoice.payment_failed') {
            $this->handlePaymentFailed($event->data->object);
        }

        return response()->json(['received' => true]);
    }

    // ── Success redirect ───────────────────────────────────────

    public function success(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('dashboard')
            ->with('success', '🎉 Welcome to Pro! Enjoy unlimited resume generations.');
    }

    // ── Webhook handlers ───────────────────────────────────────

    private function handleCheckoutComplete(object $session): void
    {
        $userId = $session->metadata->user_id ?? null;
        $plan   = (string) ($session->metadata->plan ?? 'pro_monthly');

        if (! $userId) {
            return;
        }

        $user = User::find((int) $userId);
        if (! $user instanceof User) {
            return;
        }

        $stripeSubscription = $this->stripe->subscriptions->retrieve((string) $session->subscription);

        Subscription::updateOrCreate(
            ['stripe_subscription_id' => $stripeSubscription->id],
            [
                'user_id'              => $user->id,
                'stripe_price_id'      => $stripeSubscription->items->data[0]->price->id,
                'plan'                 => $plan,
                'status'               => $stripeSubscription->status,
                'current_period_start' => now()->createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end'   => now()->createFromTimestamp($stripeSubscription->current_period_end),
            ]
        );

        $user->update([
            'plan'                   => 'pro',
            'stripe_customer_id'     => (string) $session->customer,
            'stripe_subscription_id' => (string) $stripeSubscription->id,
            'subscription_ends_at'   => now()->createFromTimestamp($stripeSubscription->current_period_end),
        ]);
    }

    private function handleSubscriptionUpdated(object $subscription): void
    {
        Subscription::where('stripe_subscription_id', $subscription->id)
            ->update(['status' => $subscription->status]);
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $sub = Subscription::where('stripe_subscription_id', $subscription->id)->first();
        if (! $sub instanceof Subscription) {
            return;
        }

        $sub->update(['status' => 'canceled', 'canceled_at' => now()]);

        $owner = $sub->user;
        if ($owner instanceof User) {
            $owner->update(['plan' => 'free', 'credits' => 0]);
        }
    }

    private function handlePaymentFailed(object $invoice): void
    {
        Log::warning('Stripe payment failed', ['customer' => $invoice->customer ?? 'unknown']);
    }
}