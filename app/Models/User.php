<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'plan',
        'credits',
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_ends_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'stripe_customer_id',
        'stripe_subscription_id',
    ];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'subscription_ends_at'=> 'datetime',
        'password'            => 'hashed',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function resumes()
    {
        return $this->hasMany(Resume::class)->latest();
    }

    public function coverLetters()
    {
        return $this->hasMany(CoverLetter::class)->latest();
    }

    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class)->latest();
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    // ── Plan helpers ───────────────────────────────────────────

    public function isPro(): bool
    {
        return $this->plan === 'pro' || $this->plan === 'annual';
    }

    public function hasCredits(): bool
    {
        return $this->isPro() || $this->credits > 0;
    }

    public function canGenerate(): bool
    {
        return $this->hasCredits();
    }

    public function deductCredit(): void
    {
        if (! $this->isPro() && $this->credits > 0) {
            // Use explicit assignment + save so IDEs don't complain about decrement() signature
            $this->credits = max(0, (int) $this->credits - 1);
            $this->save();
        }
    }

    public function creditsDisplay(): string
    {
        if ($this->isPro()) return 'Unlimited';
        return $this->credits . ' remaining';
    }
}