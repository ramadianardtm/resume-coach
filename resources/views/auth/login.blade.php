@extends('layouts.app')

@section('title', 'Sign In — ResumeCoach AI')

@section('content')
<div style="min-height:calc(100vh - 61px); display:flex; align-items:center; justify-content:center; padding:2rem;">
    <div style="width:100%; max-width:420px;">

        <div style="text-align:center; margin-bottom:2rem;">
            <h1 style="font-family:var(--serif); font-size:2rem; margin-bottom:.35rem;">Welcome back</h1>
            <p style="color:var(--muted); font-size:.9rem;">Sign in to continue building your resume.</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input class="form-control @error('email') is-invalid @enderror"
                           type="email" id="email" name="email"
                           value="{{ old('email') }}" placeholder="you@example.com"
                           autofocus autocomplete="email">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control @error('password') is-invalid @enderror"
                           type="password" id="password" name="password"
                           placeholder="Your password" autocomplete="current-password">
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
                    <label style="display:flex; align-items:center; gap:.5rem; font-size:.875rem; color:var(--muted); cursor:pointer;">
                        <input type="checkbox" name="remember" style="accent-color:var(--accent);">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:.75rem;">
                    Sign in →
                </button>
            </form>
        </div>

        <p style="text-align:center; margin-top:1.25rem; font-size:.875rem; color:var(--muted);">
            Don't have an account?
            <a href="{{ route('register') }}" style="color:var(--ink); font-weight:500;">Create one free</a>
        </p>
    </div>
</div>
@endsection