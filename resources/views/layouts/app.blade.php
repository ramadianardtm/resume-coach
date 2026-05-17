<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ResumeCoach AI')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f0e0c; --paper: #faf8f4; --cream: #f2ede4;
            --accent: #c8432a; --accent-light: #fdf0ed;
            --muted: #7a7570; --border: #e0d9ce;
            --success: #2a7a4a; --radius: 4px;
            --serif: 'DM Serif Display', Georgia, serif;
            --sans: 'DM Sans', system-ui, sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--sans); background: var(--paper); color: var(--ink); min-height: 100vh; font-size: 16px; line-height: 1.6; }

        /* NAV */
        .app-nav { display:flex; align-items:center; justify-content:space-between; padding:1rem 2rem; border-bottom:1px solid var(--border); background:white; position:sticky; top:0; z-index:100; }
        .nav-logo { font-family:var(--serif); font-size:1.3rem; color:var(--ink); text-decoration:none; }
        .nav-logo span { color:var(--accent); }
        .nav-right { display:flex; gap:1rem; align-items:center; }
        .nav-link { font-size:.875rem; color:var(--muted); text-decoration:none; }
        .nav-link:hover { color:var(--ink); }
        .nav-badge { display:inline-flex; align-items:center; gap:.4rem; font-size:.8rem; color:var(--muted); background:var(--cream); border:1px solid var(--border); padding:.3rem .75rem; border-radius:100px; }
        .nav-badge strong { color:var(--ink); }
        .btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.2rem; border-radius:var(--radius); font-family:var(--sans); font-size:.875rem; font-weight:500; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
        .btn-primary { background:var(--accent); color:white; }
        .btn-primary:hover { background:#b03825; }
        .btn-outline { background:transparent; border:1px solid var(--border); color:var(--muted); }
        .btn-outline:hover { border-color:var(--ink); color:var(--ink); }
        .btn-dark { background:var(--ink); color:white; }
        .btn-dark:hover { background:#333; }
        .btn-sm { padding:.4rem .85rem; font-size:.8rem; }
        .btn-danger { background:transparent; border:1px solid #f5ccc5; color:var(--accent); }
        .btn-danger:hover { background:var(--accent-light); }

        /* ALERTS */
        .alert { padding:.85rem 1.25rem; border-radius:var(--radius); margin-bottom:1.25rem; font-size:.875rem; }
        .alert-success { background:#f0faf4; border:1px solid #c0dece; color:var(--success); }
        .alert-error { background:var(--accent-light); border:1px solid #f5ccc5; color:var(--accent); }

        /* MAIN CONTENT */
        .page-content { max-width:1100px; margin:0 auto; padding:2.5rem 2rem; }
        .page-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:2rem; }
        .page-title { font-family:var(--serif); font-size:2rem; }
        .page-subtitle { color:var(--muted); font-size:.9rem; margin-top:.25rem; }

        /* CARDS */
        .card { background:white; border:1px solid var(--border); border-radius:8px; padding:1.5rem; }
        .card + .card { margin-top:1rem; }
        .card-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:1.25rem; }
        .doc-card { background:white; border:1px solid var(--border); border-radius:8px; padding:1.25rem; transition:border-color .15s; }
        .doc-card:hover { border-color:var(--accent); }
        .doc-card-title { font-weight:600; font-size:.95rem; margin-bottom:.25rem; }
        .doc-card-meta { font-size:.78rem; color:var(--muted); margin-bottom:1rem; }
        .doc-card-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
        .status-badge { display:inline-block; font-size:.7rem; font-weight:600; padding:.15rem .5rem; border-radius:100px; text-transform:uppercase; letter-spacing:.05em; }
        .status-complete { background:#f0faf4; color:var(--success); }
        .status-draft { background:var(--cream); color:var(--muted); }

        /* EMPTY STATE */
        .empty-state { text-align:center; padding:4rem 2rem; color:var(--muted); }
        .empty-state h3 { font-family:var(--serif); font-size:1.5rem; color:var(--ink); margin-bottom:.5rem; }
        .empty-state p { margin-bottom:1.5rem; font-size:.9rem; }

        /* FORMS */
        .form-group { margin-bottom:1.25rem; }
        .form-label { display:block; font-size:.875rem; font-weight:500; margin-bottom:.4rem; }
        .form-control { width:100%; padding:.65rem .9rem; border:1px solid var(--border); border-radius:var(--radius); font-family:var(--sans); font-size:.9rem; background:white; color:var(--ink); transition:border-color .15s; outline:none; }
        .form-control:focus { border-color:var(--accent); }
        .form-error { font-size:.78rem; color:var(--accent); margin-top:.25rem; }
    </style>
    @stack('styles')
</head>
<body>
<nav class="app-nav">
    <a href="{{ route('home') }}" class="nav-logo">Resume<span>Coach</span></a>
    <div class="nav-right">
        @auth
            @if(Auth::user()->isPro())
                <span class="nav-badge">✦ Pro</span>
            @else
                <span class="nav-badge">⚡ <strong>{{ Auth::user()->credits }}</strong> credits left</span>
                <a href="{{ route('billing.upgrade') }}" class="btn btn-primary btn-sm">Upgrade to Pro</a>
            @endif
            <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="nav-link">Login</a>
            <a href="{{ route('register') }}" class="btn btn-dark btn-sm">Get started free</a>
        @endauth
    </div>
</nav>

<main>
    @yield('content')
</main>

@stack('scripts')
</body>
</html>