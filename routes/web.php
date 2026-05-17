<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
    $content = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        <url><loc>https://resumecoach.online/</loc><priority>1.0</priority></url>
        <url><loc>https://resumecoach.online/register</loc><priority>0.8</priority></url>
        <url><loc>https://resumecoach.online/login</loc><priority>0.5</priority></url>
    </urlset>';
    return response($content, 200)->header('Content-Type', 'application/xml');
});

// ── Public landing ─────────────────────────────────────────────
Route::get('/', fn() => view('landing'))->name('home');

// ── Auth (guests only) ─────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Authenticated app ──────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Resumes ────────────────────────────────────────────────
    // Static routes BEFORE wildcard /{resumeId} to prevent collisions
    Route::prefix('resume')->name('resume.')->group(function () {
        Route::get('/',                   [ResumeController::class, 'index'])->name('index');
        Route::post('/create',            [ResumeController::class, 'create'])->name('create');
        // Wildcard routes — constrained to numbers only
        Route::get('/{resumeId}/builder', [ResumeController::class, 'builder'])->name('builder')->whereNumber('resumeId');
        Route::get('/{resumeId}',         [ResumeController::class, 'show'])->name('show')->whereNumber('resumeId');
        Route::patch('/{resumeId}',       [ResumeController::class, 'update'])->name('update')->whereNumber('resumeId');
        Route::delete('/{resumeId}',      [ResumeController::class, 'destroy'])->name('destroy')->whereNumber('resumeId');
    });

    // ── Cover Letters ──────────────────────────────────────────
    // GET /cover/create must come BEFORE /{coverId} wildcard
    Route::prefix('cover')->name('cover.')->group(function () {
        Route::get('/',                   [CoverLetterController::class, 'index'])->name('index');
        // GET create — redirects to POST create after optionally reading resume_id query param
        Route::get('/create',             [CoverLetterController::class, 'createRedirect'])->name('create.get');
        Route::post('/create',            [CoverLetterController::class, 'create'])->name('create');
        // Wildcard routes — numbers only, so "create" string never matches
        Route::get('/{coverId}/builder',  [CoverLetterController::class, 'builder'])->name('builder')->whereNumber('coverId');
        Route::get('/{coverId}',          [CoverLetterController::class, 'show'])->name('show')->whereNumber('coverId');
        Route::delete('/{coverId}',       [CoverLetterController::class, 'destroy'])->name('destroy')->whereNumber('coverId');
    });

    // ── Billing ────────────────────────────────────────────────
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/upgrade',          [BillingController::class, 'upgrade'])->name('upgrade');
        Route::get('/success',          [BillingController::class, 'success'])->name('success');
        Route::post('/paypal/activate', [BillingController::class, 'paypalActivate'])->name('paypal.activate');
    });
});

// ── PayPal Webhook (no CSRF, no auth) ─────────────────────────
Route::post('/paypal/webhook', [BillingController::class, 'paypalWebhook'])
    ->name('paypal.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
