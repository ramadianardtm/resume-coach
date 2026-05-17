<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResumeCoach AI — Your Career Coach, Not Just a Builder</title>
    <link rel="icon" type="image/png" href="{{ asset('resume-coach-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --ink: #0f0e0c;
            --paper: #faf8f4;
            --cream: #f2ede4;
            --accent: #c8432a;
            --accent-light: #fdf0ed;
            --muted: #7a7570;
            --border: #e0d9ce;
            --success: #2a7a4a;
            --radius: 4px;
            --serif: 'DM Serif Display', Georgia, serif;
            --sans: 'DM Sans', system-ui, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--sans);
            background: var(--paper);
            color: var(--ink);
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 2.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--paper);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-family: var(--serif);
            font-size: 1.4rem;
            color: var(--ink);
            text-decoration: none;
        }

        .logo span {
            color: var(--accent);
        }

        .nav-cta {
            background: var(--ink);
            color: var(--paper);
            border: none;
            padding: .55rem 1.4rem;
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: .9rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }

        .nav-cta:hover {
            background: var(--accent);
        }

        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            max-width: 1200px;
            margin: 0 auto;
            padding: 5rem 2.5rem 4rem;
            align-items: center;
        }

        .hero-left h1 {
            font-family: var(--serif);
            font-size: clamp(2.4rem, 4vw, 3.8rem);
            line-height: 1.15;
            letter-spacing: -.02em;
            margin-bottom: 1.25rem;
        }

        .hero-left h1 em {
            font-style: italic;
            color: var(--accent);
        }

        .hero-left p {
            font-size: 1.1rem;
            color: var(--muted);
            max-width: 440px;
            margin-bottom: 2rem;
            line-height: 1.7;
        }

        .hero-badges {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .8rem;
            font-weight: 500;
            padding: .35rem .75rem;
            border-radius: 100px;
            border: 1px solid var(--border);
            color: var(--muted);
            background: white;
        }

        .badge.green {
            color: var(--success);
            border-color: #c0dece;
            background: #f0faf4;
        }

        .badge.red {
            color: var(--accent);
            border-color: #f5ccc5;
            background: var(--accent-light);
        }

        .cta-group {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
            border: none;
            padding: .85rem 2rem;
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(200, 67, 42, .3);
            text-decoration: none;
        }

        .btn-primary:hover {
            background: #b03825;
        }

        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
            padding: .85rem 1.5rem;
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: .95rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-ghost:hover {
            border-color: var(--ink);
            color: var(--ink);
        }

        .hero-right {
            display: flex;
            justify-content: flex-end;
            padding-left: 3rem;
        }

        .mockup-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.75rem;
            width: 340px;
            box-shadow: 0 8px 40px rgba(15, 14, 12, .08);
            position: relative;
        }

        .mockup-card::before {
            content: 'AI Coach';
            position: absolute;
            top: -1px;
            left: 20px;
            background: var(--accent);
            color: white;
            font-size: .7rem;
            font-weight: 600;
            padding: .2rem .6rem;
            border-radius: 0 0 4px 4px;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .chat-msg {
            margin-bottom: 1rem;
            font-size: .88rem;
            line-height: 1.5;
        }

        .chat-msg.ai {
            background: var(--cream);
            border-radius: 0 8px 8px 8px;
            padding: .65rem .85rem;
        }

        .chat-msg.user {
            background: var(--ink);
            color: white;
            border-radius: 8px 0 8px 8px;
            padding: .65rem .85rem;
            text-align: right;
        }

        .chat-indicator {
            display: flex;
            gap: 4px;
            padding: .65rem .85rem;
            background: var(--cream);
            border-radius: 0 8px 8px 8px;
            width: fit-content;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--muted);
            animation: bounce 1.2s infinite;
        }

        .dot:nth-child(2) {
            animation-delay: .2s
        }

        .dot:nth-child(3) {
            animation-delay: .4s
        }

        @keyframes bounce {

            0%,
            60%,
            100% {
                transform: translateY(0)
            }

            30% {
                transform: translateY(-4px)
            }
        }

        .divider-section {
            background: var(--cream);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 3rem 2.5rem;
            text-align: center;
        }

        .divider-section h2 {
            font-family: var(--serif);
            font-size: 1.8rem;
            margin-bottom: .5rem;
        }

        .divider-section p {
            color: var(--muted);
            max-width: 560px;
            margin: 0 auto 2.5rem;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            max-width: 900px;
            margin: 0 auto;
            text-align: left;
        }

        .step-num {
            font-family: var(--serif);
            font-size: 3rem;
            color: var(--border);
            line-height: 1;
            margin-bottom: .5rem;
        }

        .step h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: .35rem;
        }

        .step p {
            font-size: .88rem;
            color: var(--muted);
            line-height: 1.6;
        }

        .pricing-section {
            padding: 4rem 2.5rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .pricing-section h2 {
            font-family: var(--serif);
            font-size: 2rem;
            text-align: center;
            margin-bottom: .5rem;
        }

        .pricing-section>p {
            text-align: center;
            color: var(--muted);
            margin-bottom: 3rem;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .plan {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 2rem;
            background: white;
        }

        .plan.featured {
            border-color: var(--accent);
            border-width: 2px;
            position: relative;
        }

        .plan.featured::before {
            content: 'Most Popular';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent);
            color: white;
            font-size: .75rem;
            font-weight: 600;
            padding: .2rem .75rem;
            border-radius: 100px;
            white-space: nowrap;
        }

        .plan-name {
            font-weight: 600;
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            margin-bottom: .5rem;
        }

        .plan-price {
            font-family: var(--serif);
            font-size: 2.5rem;
            margin-bottom: .25rem;
        }

        .plan-price span {
            font-family: var(--sans);
            font-size: .9rem;
            font-weight: 400;
            color: var(--muted);
        }

        .plan-desc {
            font-size: .88rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .plan ul {
            list-style: none;
            margin-bottom: 1.5rem;
        }

        .plan ul li {
            font-size: .88rem;
            padding: .3rem 0;
            display: flex;
            gap: .5rem;
        }

        .plan ul li::before {
            content: '✓';
            color: var(--success);
            font-weight: 700;
            flex-shrink: 0;
        }

        .plan ul li.no::before {
            content: '×';
            color: var(--border);
        }

        .plan ul li.no {
            color: var(--muted);
        }

        .plan-btn {
            width: 100%;
            padding: .75rem;
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .plan-btn.outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--ink);
        }

        .plan-btn.outline:hover {
            border-color: var(--ink);
        }

        .plan-btn.filled {
            background: var(--accent);
            border: none;
            color: white;
        }

        .plan-btn.filled:hover {
            background: #b03825;
        }

        footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid var(--border);
            font-size: .82rem;
            color: var(--muted);
        }

        @media(max-width:768px) {
            .hero {
                grid-template-columns: 1fr;
                padding: 2.5rem 1.5rem
            }

            .hero-right {
                display: none
            }

            .steps,
            .pricing-grid {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>
    <nav>
        <a class="logo" href="{{ route('home') }}">Resume<span>Coach</span></a>
        @auth
            <a href="{{ route('dashboard') }}" class="nav-cta">Go to Dashboard →</a>
        @else
            <a href="{{ route('register') }}" class="nav-cta">Start for Free →</a>
        @endauth
    </nav>

    @if (session('success'))
        <div
            style="background:#f0faf4;border-bottom:1px solid #c0dece;color:#2a7a4a;text-align:center;padding:.75rem;font-size:.875rem;">
            {{ session('success') }}
        </div>
    @endif

    <section class="hero">
        <div class="hero-left">
            <h1>Your AI career coach<br>that actually <em>listens</em><br>to your story.</h1>
            <p>Most resume builders ask you to fill out forms. We have a conversation. Our AI interviews you like a
                career coach, then builds a job-tailored resume and cover letter from your real experience.</p>
            <div class="hero-badges">
                <span class="badge green">✓ No dark-pattern billing</span>
                <span class="badge green">✓ 3 free generations</span>
                <span class="badge red">★ ATS-optimised output</span>
            </div>
            <div class="cta-group">
                <a href="{{ route('register') }}" class="btn-primary">Build my resume — free</a>
                <a href="#how" class="btn-ghost">See how it works</a>
            </div>
        </div>
        <div class="hero-right">
            <div class="mockup-card">
                <div class="chat-msg ai">Hi! I'm your career coach. Before we build anything, tell me — what role are
                    you applying for?</div>
                <div class="chat-msg user">Senior Product Manager at a fintech startup</div>
                <div class="chat-msg ai">Great choice. What's the most impactful project you've shipped in the last 2
                    years? Give me the messy details — metrics, team size, obstacles.</div>
                <div class="chat-msg user">Led a payments API redesign, cut latency 40%, team of 8...</div>
                <div class="chat-indicator">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="divider-section" id="how">
        <h2>Three steps. Zero forms.</h2>
        <p>Instead of making you fill out fields, our AI coaches you through a natural conversation and writes
            everything for you.</p>
        <div class="steps">
            <div class="step">
                <div class="step-num">01</div>
                <h3>Tell us the role</h3>
                <p>Paste a job description or describe what you're applying for. The AI tailors everything specifically
                    to that position.</p>
            </div>
            <div class="step">
                <div class="step-num">02</div>
                <h3>Get interviewed by AI</h3>
                <p>Our coach asks smart questions to surface your best achievements — the ones you'd forget to put on a
                    form.</p>
            </div>
            <div class="step">
                <div class="step-num">03</div>
                <h3>Download and apply</h3>
                <p>Get a polished, ATS-optimised resume + a personalised cover letter. Download as PDF and apply with
                    confidence.</p>
            </div>
        </div>
    </section>

    <section class="pricing-section">
        <h2>Honest pricing. No traps.</h2>
        <p>You get 3 full generations free — resume + cover letter — with no credit card required. Pay only when you're
            ready.</p>
        <div class="pricing-grid">
            <div class="plan">
                <div class="plan-name">Free</div>
                <div class="plan-price">$0 <span>forever</span></div>
                <div class="plan-desc">Perfect for your first application</div>
                <ul>
                    <li>3 resume + cover letter generations</li>
                    <li>AI coaching conversation</li>
                    <li>ATS keyword optimisation</li>
                    <li>PDF download</li>
                    <li class="no">Unlimited generations</li>
                    <li class="no">Multiple resume versions</li>
                </ul>
                <a href="{{ route('register') }}" class="plan-btn outline">Start free</a>
            </div>
            <div class="plan featured">
                <div class="plan-name">Pro</div>
                <div class="plan-price">$4 <span>/month</span></div>
                <div class="plan-desc">For active job seekers applying to multiple roles</div>
                <ul>
                    <li>Unlimited generations</li>
                    <li>Tailored to each job description</li>
                    <li>Multiple saved resume versions</li>
                    <li>Cover letter for every application</li>
                    <li>Priority ATS optimisation</li>
                    <li>Cancel anytime — no tricks</li>
                </ul>
                <a href="{{ route('register') }}" class="plan-btn filled">Get Pro — $4/mo</a>
            </div>
        </div>
    </section>

    <footer>&copy; {{ date('Y') }} ResumeCoach AI &nbsp;·&nbsp; No hidden fees &nbsp;·&nbsp; Cancel anytime</footer>
</body>

</html>
