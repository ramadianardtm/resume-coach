<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * Stripe webhooks must be excluded because Stripe sends raw POST
     * requests with no Laravel session/cookie — they'd always fail CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        'stripe/webhook',
    ];
}