@extends('layouts.app')

@section('title', 'Create Account — ResumeCoach AI')

@section('content')
<div style="min-height:calc(100vh - 61px); display:flex; align-items:center; justify-content:center; padding:2rem;">
    <div style="width:100%; max-width:420px;">

        <div style="text-align:center; margin-bottom:2rem;">
            <h1 style="font-family:var(--serif); font-size:2rem; margin-bottom:.35rem;">Create your account</h1>
            <p style="color:var(--muted); font-size:.9rem;">3 free resume generations. No credit card needed.</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="card">
            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Full name</label>
                    <input class="form-control @error('name') is-invalid @enderror"
                           type="text" id="name" name="name"
                           value="{{ old('name') }}" placeholder="Steve Jobs" autofocus autocomplete="name">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input class="form-control @error('email') is-invalid @enderror"
                           type="email" id="email" name="email"
                           value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control @error('password') is-invalid @enderror"
                           type="password" id="password" name="password"
                           placeholder="Min. 8 characters" autocomplete="new-password">
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input class="form-control"
                           type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Repeat your password" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:.75rem;">
                    Create free account →
                </button>
            </form>
        </div>

        <p style="text-align:center; margin-top:1.25rem; font-size:.875rem; color:var(--muted);">
            Already have an account?
            <a href="{{ route('login') }}" style="color:var(--ink); font-weight:500;">Sign in</a>
        </p>

        <p style="text-align:center; margin-top:1rem; font-size:.78rem; color:var(--muted);">
            By creating an account you agree to our terms. No dark-pattern billing — ever.
        </p>
    </div>
</div>
@endsection