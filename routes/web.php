<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

// ── Public landing page ────────────────────────────────────────
Route::get('/', fn () => view('landing'))->name('home');

// ── Auth ───────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Authenticated app ──────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resumes
    Route::prefix('resume')->name('resume.')->group(function () {
        Route::get('/',                      [ResumeController::class, 'index'])->name('index');
        Route::post('/create',               [ResumeController::class, 'create'])->name('create');
        Route::get('/{resumeId}/builder',    [ResumeController::class, 'builder'])->name('builder');
        Route::get('/{resumeId}',            [ResumeController::class, 'show'])->name('show');
        Route::patch('/{resumeId}',          [ResumeController::class, 'update'])->name('update');
        Route::delete('/{resumeId}',         [ResumeController::class, 'destroy'])->name('destroy');
    });

    // Cover letters
    Route::prefix('cover')->name('cover.')->group(function () {
        Route::get('/',                      [CoverLetterController::class, 'index'])->name('index');
        Route::post('/create',               [CoverLetterController::class, 'create'])->name('create');
        Route::get('/{coverId}/builder',     [CoverLetterController::class, 'builder'])->name('builder');
        Route::get('/{coverId}',             [CoverLetterController::class, 'show'])->name('show');
        Route::delete('/{coverId}',          [CoverLetterController::class, 'destroy'])->name('destroy');
    });

    // Billing
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/upgrade',           [BillingController::class, 'upgrade'])->name('upgrade');
        Route::post('/checkout',         [BillingController::class, 'checkout'])->name('checkout');
        Route::get('/success',           [BillingController::class, 'success'])->name('success');
    });
});

// ── Stripe webhook (no CSRF) ───────────────────────────────────
Route::post('/stripe/webhook', [BillingController::class, 'webhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware(['web', \App\Http\Middleware\VerifyCsrfToken::class]);