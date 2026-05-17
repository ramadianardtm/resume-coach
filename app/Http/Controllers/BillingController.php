<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillingController extends BaseController
{
    // ── Upgrade page ───────────────────────────────────────────

    public function upgrade(): \Illuminate\View\View
    {
        /** @var User $user */
        $user = Auth::user();
        return view('billing.upgrade', compact('user'));
    }

    // ── PayPal: activate subscription after JS onApprove ───────
    //
    // After the user completes PayPal's subscription flow, the JS
    // calls this endpoint with data.subscriptionID. We verify it
    // with PayPal's REST API, then mark the user as Pro.

    public function paypalActivate(Request $request): JsonResponse
    {
        $request->validate([
            'subscription_id' => ['required', 'string'],
            'plan'            => ['required', 'in:monthly,annual'],
        ]);

        /** @var User $user */
        $user           = Auth::user();
        $subscriptionId = $request->string('subscription_id')->toString();
        $plan           = $request->string('plan')->toString();

        // Verify with PayPal
        $verified = $this->verifyPayPalSubscription($subscriptionId);

        if (! $verified) {
            Log::warning('PayPal subscription verification failed', [
                'user_id' => $user->id,
                'sub_id'  => $subscriptionId,
            ]);
            return response()->json(['success' => false, 'message' => 'Verification failed. Please contact support.'], 400);
        }

        // Save subscription record
        Subscription::updateOrCreate(
            ['stripe_subscription_id' => $subscriptionId],
            [
                'user_id'              => $user->id,
                'stripe_price_id'      => $plan,
                'plan'                 => $plan,
                'status'               => 'active',
                'current_period_start' => now(),
                'current_period_end'   => $plan === 'annual' ? now()->addYear() : now()->addMonth(),
            ]
        );

        // Activate Pro
        $user->update([
            'plan'                   => 'pro',
            'stripe_subscription_id' => $subscriptionId,
            'subscription_ends_at'   => $plan === 'annual' ? now()->addYear() : now()->addMonth(),
        ]);

        Log::info('PayPal Pro activated', ['user_id' => $user->id, 'plan' => $plan]);

        return response()->json(['success' => true]);
    }

    // ── Success page ───────────────────────────────────────────

    public function success(): RedirectResponse
    {
        return redirect()->route('dashboard')
            ->with('success', '🎉 Welcome to Pro! You now have unlimited resume and cover letter generations.');
    }

    // ── PayPal Webhook ─────────────────────────────────────────
    // Set up in PayPal Dashboard → Webhooks → your domain/paypal/webhook
    // Events: BILLING.SUBSCRIPTION.CANCELLED, BILLING.SUBSCRIPTION.EXPIRED

    public function paypalWebhook(Request $request): JsonResponse
    {
        $eventType = $request->input('event_type', '');

        Log::info('PayPal webhook', ['event' => $eventType]);

        if (in_array($eventType, ['BILLING.SUBSCRIPTION.CANCELLED', 'BILLING.SUBSCRIPTION.EXPIRED'], true)) {
            $subId = $request->input('resource.id');
            if ($subId) {
                $sub = Subscription::where('stripe_subscription_id', $subId)->first();
                if ($sub instanceof Subscription) {
                    $sub->update(['status' => 'canceled', 'canceled_at' => now()]);
                    $owner = $sub->user;
                    if ($owner instanceof User) {
                        $owner->update(['plan' => 'free', 'credits' => 0]);
                    }
                }
            }
        }

        return response()->json(['received' => true]);
    }

    // ── Private: get PayPal access token then verify sub ───────

    private function verifyPayPalSubscription(string $subscriptionId): bool
    {
        try {
            $tokenRes = Http::asForm()->withBasicAuth(
                (string) config('services.paypal.client_id'),
                (string) config('services.paypal.secret')
            )->post(config('services.paypal.base_url') . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

            if ($tokenRes->failed()) return false;

            $token = $tokenRes->json('access_token');

            $subRes = Http::withToken($token)
                ->get(config('services.paypal.base_url') . '/v1/billing/subscriptions/' . $subscriptionId);

            if ($subRes->failed()) return false;

            return in_array($subRes->json('status'), ['ACTIVE', 'APPROVAL_PENDING'], true);

        } catch (\Exception $e) {
            Log::error('PayPal verify exception', ['msg' => $e->getMessage()]);
            return false;
        }
    }
}