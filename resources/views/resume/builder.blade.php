<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <title>Resume Builder — ResumeCoach AI</title>
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
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .85rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: white;
            flex-shrink: 0;
        }

        .app-logo {
            font-family: var(--serif);
            font-size: 1.2rem;
            color: var(--ink);
            text-decoration: none;
        }

        .app-logo span {
            color: var(--accent);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .credits-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .8rem;
            color: var(--muted);
            background: var(--cream);
            border: 1px solid var(--border);
            padding: .3rem .75rem;
            border-radius: 100px;
        }

        .credits-pill strong {
            color: var(--ink);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: .45rem 1rem;
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: .82rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all .15s;
            text-decoration: none;
        }

        .btn-dark {
            background: var(--ink);
            color: white;
        }

        .btn-dark:hover {
            background: #333;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .btn-outline:hover {
            border-color: var(--ink);
            color: var(--ink);
        }

        .btn-accent {
            background: var(--accent);
            color: white;
        }

        .btn-accent:hover {
            background: #b03825;
        }

        .btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        /* Body layout */
        .app-body {
            display: grid;
            grid-template-columns: 380px 1fr;
            flex: 1;
            overflow: hidden;
        }

        /* ── Chat Panel ── */
        .chat-panel {
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            background: white;
            overflow: hidden;
        }

        .mode-tabs {
            display: flex;
            padding: .4rem;
            background: var(--cream);
            border-bottom: 1px solid var(--border);
            gap: .25rem;
        }

        .mode-tab {
            flex: 1;
            padding: .4rem;
            font-size: .8rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            background: transparent;
            color: var(--muted);
            transition: all .15s;
            font-family: var(--sans);
        }

        .mode-tab.active {
            background: white;
            color: var(--ink);
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .chat-header {
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--border);
            background: var(--cream);
        }

        .chat-header h3 {
            font-family: var(--serif);
            font-size: .95rem;
            margin-bottom: .1rem;
        }

        .chat-header p {
            font-size: .75rem;
            color: var(--muted);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: .65rem;
        }

        .msg {
            max-width: 90%;
            font-size: .855rem;
            line-height: 1.55;
            border-radius: 8px;
            padding: .6rem .85rem;
            animation: fadeUp .2s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(4px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .msg.ai {
            background: var(--cream);
            color: var(--ink);
            border-radius: 0 8px 8px 8px;
            align-self: flex-start;
        }

        .msg.user {
            background: var(--ink);
            color: white;
            border-radius: 8px 0 8px 8px;
            align-self: flex-end;
        }

        .msg.system {
            background: var(--accent-light);
            color: var(--accent);
            border-radius: 6px;
            align-self: center;
            font-size: .78rem;
            text-align: center;
            max-width: 100%;
            padding: .45rem .75rem;
        }

        .typing {
            display: flex;
            gap: 4px;
            padding: .6rem .85rem;
            background: var(--cream);
            border-radius: 0 8px 8px 8px;
            align-self: flex-start;
        }

        .dot {
            width: 5px;
            height: 5px;
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

        .chat-input-area {
            border-top: 1px solid var(--border);
            padding: .75rem;
            background: var(--paper);
        }

        .input-row {
            display: flex;
            gap: .4rem;
            align-items: flex-end;
        }

        .chat-input {
            flex: 1;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: .55rem .8rem;
            font-family: var(--sans);
            font-size: .855rem;
            resize: none;
            background: white;
            color: var(--ink);
            min-height: 38px;
            max-height: 110px;
            overflow-y: auto;
            line-height: 1.5;
            outline: none;
            transition: border-color .15s;
        }

        .chat-input:focus {
            border-color: var(--accent);
        }

        .chat-input::placeholder {
            color: var(--muted);
        }

        .send-btn {
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 6px;
            padding: .55rem .9rem;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            height: 38px;
            flex-shrink: 0;
            transition: background .15s;
            font-family: var(--sans);
        }

        .send-btn:hover {
            background: #b03825;
        }

        .send-btn:disabled {
            background: var(--border);
            cursor: not-allowed;
        }

        /* ── Preview Panel ── */
        .preview-panel {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .65rem 1.25rem;
            border-bottom: 1px solid var(--border);
            background: white;
            flex-shrink: 0;
        }

        .preview-toolbar h3 {
            font-size: .78rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .toolbar-right {
            display: flex;
            gap: .4rem;
        }

        .preview-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            justify-content: center;
            background: var(--paper);
        }

        /* Resume sheet */
        .resume-sheet {
            background: white;
            border: 1px solid var(--border);
            border-radius: 4px;
            width: 100%;
            max-width: 680px;
            min-height: 860px;
            padding: 2.75rem 3rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .06);
            font-size: .855rem;
            line-height: 1.55;
        }

        .resume-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 500px;
            color: var(--muted);
            text-align: center;
            gap: .75rem;
        }

        .resume-placeholder .icon {
            font-size: 2.5rem;
            opacity: .25;
        }

        .resume-placeholder h3 {
            font-family: var(--serif);
            font-size: 1.3rem;
            color: var(--ink);
        }

        .resume-placeholder p {
            font-size: .855rem;
            max-width: 300px;
        }

        /* Resume content */
        .r-head {
            margin-bottom: 1.25rem;
            border-bottom: 2px solid var(--ink);
            padding-bottom: .85rem;
        }

        .r-name {
            font-family: var(--serif);
            font-size: 1.8rem;
            line-height: 1.1;
            margin-bottom: .2rem;
        }

        .r-contact {
            font-size: .775rem;
            color: var(--muted);
            display: flex;
            gap: .85rem;
            flex-wrap: wrap;
        }

        .r-section {
            margin-bottom: 1.1rem;
        }

        .r-section-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--accent);
            margin-bottom: .5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: .2rem;
        }

        .r-entry {
            margin-bottom: .75rem;
        }

        .r-entry-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .r-entry-title {
            font-weight: 600;
            font-size: .875rem;
        }

        .r-entry-sub {
            color: var(--muted);
            font-size: .8rem;
        }

        .r-entry-date {
            font-size: .775rem;
            color: var(--muted);
            flex-shrink: 0;
            margin-left: .5rem;
        }

        .r-entry ul {
            margin-top: .3rem;
            padding-left: 1rem;
        }

        .r-entry ul li {
            font-size: .82rem;
            line-height: 1.55;
            margin-bottom: .15rem;
        }

        .r-summary {
            font-size: .855rem;
            line-height: 1.65;
            color: #333;
        }

        .skills-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .skill-tag {
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 3px;
            padding: .18rem .48rem;
            font-size: .76rem;
        }

        /* Cover letter sheet */
        .cover-sheet {
            background: white;
            border: 1px solid var(--border);
            border-radius: 4px;
            width: 100%;
            max-width: 680px;
            min-height: 860px;
            padding: 2.75rem 3rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .06);
            font-size: .9rem;
            line-height: 1.75;
        }

        .cl-date {
            color: var(--muted);
            margin-bottom: 1.25rem;
            font-size: .855rem;
        }

        .cl-body p {
            margin-bottom: .9rem;
        }

        .cl-sign {
            margin-top: 1.75rem;
        }

        /* Upgrade notice */
        .upgrade-banner {
            background: var(--accent-light);
            border: 1px solid #f5ccc5;
            border-radius: 6px;
            padding: .75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: .75rem;
            font-size: .82rem;
        }

        .upgrade-banner a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="app-header">
        <a href="{{ route('dashboard') }}" class="app-logo">Resume<span>Coach</span></a>
        <div class="header-right">
            @if (auth()->user()->isPro())
                <span class="credits-pill">✦ Pro — Unlimited</span>
            @else
                <span class="credits-pill" id="credits-pill">
                    ⚡ <strong id="credits-count">{{ auth()->user()->credits }}</strong> credits left
                </span>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline">← Dashboard</a>
            <button class="btn btn-accent" id="btn-download" onclick="downloadPDF()" disabled>⬇ Download PDF</button>
        </div>
    </div>

    <div class="app-body">

        {{-- ── Chat Panel ── --}}
        <div class="chat-panel">
            <div class="mode-tabs">
                <button class="mode-tab active" id="tab-resume" onclick="switchMode('resume')">📄 Resume</button>
                <button class="mode-tab" id="tab-cover" onclick="switchMode('cover')">✉️ Cover Letter</button>
            </div>
            <div class="chat-header">
                <h3 id="chat-title">Resume Coach</h3>
                <p id="chat-subtitle">Answer the questions to build your resume</p>
            </div>

            @if (!auth()->user()->isPro() && auth()->user()->credits <= 0)
                <div class="upgrade-banner">
                    <span>You've used all your free credits.</span>
                    <a href="{{ route('billing.upgrade') }}">Upgrade to Pro →</a>
                </div>
            @endif

            <div class="chat-messages" id="chat-messages"></div>

            <div class="chat-input-area">
                <div class="input-row">
                    <textarea class="chat-input" id="user-input" placeholder="Type your answer here…" rows="1"
                        onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                    <button class="send-btn" id="send-btn" onclick="sendMessage()">Send</button>
                </div>
            </div>
        </div>

        {{-- ── Preview Panel ── --}}
        <div class="preview-panel">
            <div class="preview-toolbar">
                <h3 id="preview-label">Resume Preview</h3>
                <div class="toolbar-right">
                    <button class="btn btn-outline" id="btn-regen" onclick="regenerate()" disabled>↺ Regenerate</button>
                    <button class="btn btn-accent" id="btn-dl2" onclick="downloadPDF()" disabled>⬇ Download
                        PDF</button>
                </div>
            </div>
            <div class="preview-scroll">
                <div id="doc-preview">
                    <div class="resume-sheet">
                        <div class="resume-placeholder">
                            <div class="icon">📋</div>
                            <h3>Your resume will appear here</h3>
                            <p>Answer the coach's questions on the left and your resume will be built in real time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const SESSION_ID = {{ $session->id }};
        const RESUME_ID = {{ $resume->id }};
        const RESUME_DATA = @json($resumeData ?? null);
        const COVER_DATA = @json($coverData ?? null);
        const IS_PRO = {{ auth()->user()->isPro() ? 'true' : 'false' }};
        const UPGRADE_URL = "{{ route('billing.upgrade') }}";
        const API_URL = "{{ url('/api/chat') }}";
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        let mode = 'resume';
        let resumeData = null;
        let coverData = null;
        let resumeGenerated = false;

        // ── Init ───────────────────────────────────────────────────────
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const res = await fetch(`/api/chat/session/${SESSION_ID}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    }
                });
                const data = await res.json();
                const history = data.messages ?? [];

                if (history.length > 0) {
                    history.forEach(m => {
                        if (m.role === 'user' || m.role === 'assistant') {
                            addMsg(m.role === 'assistant' ? 'ai' : 'user', m.content);
                        }
                    });

                    if (RESUME_DATA && Object.keys(RESUME_DATA).length > 0) {
                        resumeData = RESUME_DATA;
                        resumeGenerated = true;
                        renderResume(resumeData);
                        enableDownload();
                    } else {
                        // ✅ No resume yet — restore the placeholder explicitly
                        showPlaceholder();
                    }

                    if (COVER_DATA) {
                        coverData = COVER_DATA;
                        // Don't render cover on resume tab — just store it
                        // renderCoverLetter(coverData); ← remove this
                        enableDownload();
                    }

                } else {
                    showPlaceholder();
                    addMsg('ai',
                        "Hi! I'm your ResumeCoach 👋 I'm going to interview you like a real career coach — no boring forms.\n\nLet's start: **What role are you applying for?**"
                    );
                }

            } catch (e) {
                showPlaceholder();
                addMsg('ai', "Hi! I'm your ResumeCoach 👋 Let's start: **What role are you applying for?**");
            }
        });

        function showPlaceholder() {
            document.getElementById('doc-preview').innerHTML = `
        <div class="resume-sheet">
            <div class="resume-placeholder">
                <div class="icon">📋</div>
                <h3>Your resume will appear here</h3>
                <p>Answer the coach's questions on the left and your resume will be built in real time.</p>
            </div>
        </div>`;
        }

        // ── Messaging ──────────────────────────────────────────────────
        function addMsg(role, text) {
            const wrap = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = `msg ${role}`;
            div.innerHTML = fmt(text);
            wrap.appendChild(div);
            wrap.scrollTop = wrap.scrollHeight;
        }

        function fmt(t) {
            return t.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
        }

        function showTyping() {
            const wrap = document.getElementById('chat-messages');
            const el = document.createElement('div');
            el.className = 'typing';
            el.id = 'typing';
            el.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
            wrap.appendChild(el);
            wrap.scrollTop = wrap.scrollHeight;
        }

        function removeTyping() {
            document.getElementById('typing')?.remove();
        }

        // ── Send ───────────────────────────────────────────────────────
        async function sendMessage() {
            const input = document.getElementById('user-input');
            const text = input.value.trim();
            if (!text) return;

            input.value = '';
            autoResize(input);
            addMsg('user', text);
            setSendDisabled(true);
            showTyping();

            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        session_id: SESSION_ID,
                        message: text
                    })
                });

                const data = await res.json();
                removeTyping();

                if (res.status === 402) {
                    addMsg('system', 'You\'ve used all your free credits. <a href="' + UPGRADE_URL +
                        '" style="color:var(--accent);font-weight:600;">Upgrade to Pro →</a>');
                    setSendDisabled(false);
                    return;
                }

                addMsg('ai', data.message || 'Something went wrong. Please try again.');

                if (data.resume_json) {
                    resumeData = data.resume_json;
                    resumeGenerated = true;
                    renderResume(resumeData);
                    enableDownload();
                    updateCredits(data.credits_left, data.is_pro);
                }

                if (data.cover_json) {
                    coverData = data.cover_json;
                    renderCoverLetter(coverData);
                    enableDownload();
                    updateCredits(data.credits_left, data.is_pro);
                }

            } catch (e) {
                removeTyping();
                addMsg('ai', 'Connection error. Please try again.');
            }

            setSendDisabled(false);
        }

        function setSendDisabled(v) {
            document.getElementById('send-btn').disabled = v;
        }

        function handleKey(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        }

        function autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 110) + 'px';
        }

        // ── Mode switch ────────────────────────────────────────────────
        function switchMode(m) {
            mode = m;
            document.getElementById('tab-resume').classList.toggle('active', m === 'resume');
            document.getElementById('tab-cover').classList.toggle('active', m === 'cover');
            document.getElementById('preview-label').textContent = m === 'resume' ? 'Resume Preview' :
                'Cover Letter Preview';

            if (m === 'cover') {
                document.getElementById('chat-title').textContent = 'Cover Letter Coach';
                document.getElementById('chat-subtitle').textContent = 'A few questions to personalise your cover letter';
                if (coverData) {
                    renderCoverLetter(coverData);
                } else if (resumeGenerated) {
                    document.getElementById('chat-messages').innerHTML = '';
                    addMsg('ai',
                        "Great — let's write your cover letter! 🎯\n\n**What company are you applying to**, and what specifically excites you about this role?"
                    );
                } else {
                    addMsg('system', 'Finish your resume first, then come back to create a cover letter.');
                    mode = 'resume';
                    document.getElementById('tab-resume').classList.add('active');
                    document.getElementById('tab-cover').classList.remove('active');
                }
            } else {
                document.getElementById('chat-title').textContent = 'Resume Coach';
                document.getElementById('chat-subtitle').textContent = 'Answer the questions to build your resume';
                if (resumeData) renderResume(resumeData);
            }
        }

        // ── Render Resume ──────────────────────────────────────────────
        function renderResume(d) {
            const exp = (d.experience || []).map(j => `
        <div class="r-entry">
            <div class="r-entry-row">
                <div><span class="r-entry-title">${j.title}</span><span class="r-entry-sub"> · ${j.company}</span></div>
                <span class="r-entry-date">${j.duration}</span>
            </div>
            <ul>${(j.bullets||[]).map(b=>`<li>${b}</li>`).join('')}</ul>
        </div>`).join('');

            const edu = (d.education || []).map(e => `
        <div class="r-entry">
            <div class="r-entry-row">
                <div><span class="r-entry-title">${e.degree}</span><span class="r-entry-sub"> · ${e.school}</span></div>
                <span class="r-entry-date">${e.year}</span>
            </div>
        </div>`).join('');

            const skills =
                `<div class="skills-wrap">${(d.skills||[]).map(s=>`<span class="skill-tag">${s}</span>`).join('')}</div>`;

            document.getElementById('doc-preview').innerHTML = `
        <div class="resume-sheet" id="printable">
            <div class="r-head">
                <div class="r-name">${d.name||''}</div>
                <div class="r-contact">
                    ${d.email?`<span>${d.email}</span>`:''}
                    ${d.phone?`<span>${d.phone}</span>`:''}
                    ${d.location?`<span>${d.location}</span>`:''}
                    ${d.linkedin?`<span>${d.linkedin}</span>`:''}
                </div>
            </div>
            ${d.summary?`<div class="r-section"><div class="r-section-title">Professional Summary</div><p class="r-summary">${d.summary}</p></div>`:''}
            ${d.experience?.length?`<div class="r-section"><div class="r-section-title">Experience</div>${exp}</div>`:''}
            ${d.education?.length?`<div class="r-section"><div class="r-section-title">Education</div>${edu}</div>`:''}
            ${d.skills?.length?`<div class="r-section"><div class="r-section-title">Skills</div>${skills}</div>`:''}
        </div>`;
        }

        // ── Render Cover Letter ────────────────────────────────────────
        function renderCoverLetter(d) {
            document.getElementById('doc-preview').innerHTML = `
        <div class="cover-sheet" id="printable">
            <p class="cl-date">${d.date||''}</p>
            <p style="margin-bottom:1.25rem;">Dear ${d.hiringManager||'Hiring Manager'},</p>
            <div class="cl-body">${(d.paragraphs||[]).map(p=>`<p>${p}</p>`).join('')}</div>
            <div class="cl-sign">
                <p>${d.signoff||'Best regards,'}</p><br>
                <p><strong>${d.name||''}</strong></p>
            </div>
        </div>`;
        }

        // ── Download ───────────────────────────────────────────────────
        function downloadPDF() {
            const el = document.getElementById('printable');
            if (!el) {
                alert('Generate your document first!');
                return;
            }

            const name = resumeData?.name || coverData?.name || 'document';
            const filename = mode === 'resume' ?
                `${name.replace(/\s+/g, '_')}_Resume.pdf` :
                `${name.replace(/\s+/g, '_')}_Cover_Letter.pdf`;

            const opt = {
                margin: [12, 14, 12, 14], // top, right, bottom, left in mm
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            // Swap button to loading state
            const btns = ['btn-download', 'btn-dl2'];
            btns.forEach(id => {
                const b = document.getElementById(id);
                if (b) {
                    b.disabled = true;
                    b.textContent = 'Generating…';
                }
            });

            html2pdf().set(opt).from(el).save().then(() => {
                btns.forEach(id => {
                    const b = document.getElementById(id);
                    if (b) {
                        b.disabled = false;
                        b.textContent = '⬇ Download PDF';
                    }
                });
            });
        }

        const win = window.open('', '_blank');
        win.document.write(
            `<!DOCTYPE html><html><head><title>Resume</title><style>${styles}</style></head><body>${el.outerHTML}</body></html>`
        );
        win.document.close();
        setTimeout(() => win.print(), 600);


        // ── Regenerate ─────────────────────────────────────────────────
        async function regenerate() {
            document.getElementById('user-input').value =
                mode === 'resume' ?
                'Please regenerate my resume with stronger action verbs and better-quantified achievements.' :
                'Please regenerate my cover letter to be more compelling and specific.';
            await sendMessage();
        }

        // ── Helpers ────────────────────────────────────────────────────
        function enableDownload() {
            ['btn-download', 'btn-dl2', 'btn-regen'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.disabled = false;
            });
        }

        function updateCredits(left, isPro) {
            if (isPro) return;
            const el = document.getElementById('credits-count');
            if (el) el.textContent = left;
        }
    </script>
</body>

</html>
