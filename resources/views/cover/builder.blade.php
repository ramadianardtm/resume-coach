<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cover Letter Builder — ResumeCoach AI</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#0f0e0c; --paper:#faf8f4; --cream:#f2ede4; --accent:#c8432a; --accent-light:#fdf0ed; --muted:#7a7570; --border:#e0d9ce; --success:#2a7a4a; --radius:4px; --serif:'DM Serif Display',Georgia,serif; --sans:'DM Sans',system-ui,sans-serif; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:var(--sans); background:var(--paper); color:var(--ink); height:100vh; overflow:hidden; display:flex; flex-direction:column; }
        .app-header { display:flex; align-items:center; justify-content:space-between; padding:.85rem 1.5rem; border-bottom:1px solid var(--border); background:white; flex-shrink:0; }
        .app-logo { font-family:var(--serif); font-size:1.2rem; color:var(--ink); text-decoration:none; }
        .app-logo span { color:var(--accent); }
        .header-right { display:flex; align-items:center; gap:.75rem; }
        .credits-pill { display:inline-flex; align-items:center; gap:.4rem; font-size:.8rem; color:var(--muted); background:var(--cream); border:1px solid var(--border); padding:.3rem .75rem; border-radius:100px; }
        .btn { display:inline-flex; align-items:center; padding:.45rem 1rem; border-radius:var(--radius); font-family:var(--sans); font-size:.82rem; font-weight:500; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
        .btn-outline { background:transparent; border:1px solid var(--border); color:var(--muted); }
        .btn-outline:hover { border-color:var(--ink); color:var(--ink); }
        .btn-accent { background:var(--accent); color:white; }
        .btn-accent:hover { background:#b03825; }
        .btn:disabled { opacity:.4; cursor:not-allowed; }
        .app-body { display:grid; grid-template-columns:380px 1fr; flex:1; overflow:hidden; }
        .chat-panel { border-right:1px solid var(--border); display:flex; flex-direction:column; background:white; overflow:hidden; }
        .chat-header { padding:.85rem 1rem; border-bottom:1px solid var(--border); background:var(--cream); }
        .chat-header h3 { font-family:var(--serif); font-size:.95rem; margin-bottom:.1rem; }
        .chat-header p { font-size:.75rem; color:var(--muted); }
        .chat-messages { flex:1; overflow-y:auto; padding:1rem; display:flex; flex-direction:column; gap:.65rem; }
        .msg { max-width:90%; font-size:.855rem; line-height:1.55; border-radius:8px; padding:.6rem .85rem; animation:fadeUp .2s ease; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(4px)} to{opacity:1;transform:translateY(0)} }
        .msg.ai { background:var(--cream); border-radius:0 8px 8px 8px; align-self:flex-start; }
        .msg.user { background:var(--ink); color:white; border-radius:8px 0 8px 8px; align-self:flex-end; }
        .typing { display:flex; gap:4px; padding:.6rem .85rem; background:var(--cream); border-radius:0 8px 8px 8px; align-self:flex-start; }
        .dot { width:5px; height:5px; border-radius:50%; background:var(--muted); animation:bounce 1.2s infinite; }
        .dot:nth-child(2){animation-delay:.2s} .dot:nth-child(3){animation-delay:.4s}
        @keyframes bounce{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-4px)}}
        .chat-input-area { border-top:1px solid var(--border); padding:.75rem; background:var(--paper); }
        .input-row { display:flex; gap:.4rem; align-items:flex-end; }
        .chat-input { flex:1; border:1px solid var(--border); border-radius:6px; padding:.55rem .8rem; font-family:var(--sans); font-size:.855rem; resize:none; background:white; color:var(--ink); min-height:38px; max-height:110px; overflow-y:auto; line-height:1.5; outline:none; transition:border-color .15s; }
        .chat-input:focus { border-color:var(--accent); }
        .chat-input::placeholder { color:var(--muted); }
        .send-btn { background:var(--accent); color:white; border:none; border-radius:6px; padding:.55rem .9rem; font-size:.82rem; font-weight:600; cursor:pointer; height:38px; flex-shrink:0; font-family:var(--sans); }
        .send-btn:hover { background:#b03825; }
        .send-btn:disabled { background:var(--border); cursor:not-allowed; }
        .preview-panel { display:flex; flex-direction:column; overflow:hidden; }
        .preview-toolbar { display:flex; align-items:center; justify-content:space-between; padding:.65rem 1.25rem; border-bottom:1px solid var(--border); background:white; flex-shrink:0; }
        .preview-toolbar h3 { font-size:.78rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; }
        .preview-scroll { flex:1; overflow-y:auto; padding:1.5rem; display:flex; justify-content:center; background:var(--paper); }
        .cover-sheet { background:white; border:1px solid var(--border); border-radius:4px; width:100%; max-width:680px; min-height:860px; padding:2.75rem 3rem; box-shadow:0 4px 24px rgba(0,0,0,.06); font-size:.9rem; line-height:1.75; }
        .cover-placeholder { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:500px; color:var(--muted); text-align:center; gap:.75rem; }
        .cover-placeholder .icon { font-size:2.5rem; opacity:.25; }
        .cover-placeholder h3 { font-family:var(--serif); font-size:1.3rem; color:var(--ink); }
        .cl-date { color:var(--muted); margin-bottom:1.25rem; font-size:.855rem; }
        .cl-body p { margin-bottom:.9rem; }
        .cl-sign { margin-top:1.75rem; }
    </style>
</head>
<body>

<div class="app-header">
    <a href="{{ route('dashboard') }}" class="app-logo">Resume<span>Coach</span></a>
    <div class="header-right">
        @if(auth()->user()->isPro())
            <span class="credits-pill">✦ Pro — Unlimited</span>
        @else
            <span class="credits-pill">⚡ <strong id="credits-count">{{ auth()->user()->credits }}</strong> credits left</span>
        @endif
        <a href="{{ route('dashboard') }}" class="btn btn-outline">← Dashboard</a>
        <button class="btn btn-accent" id="btn-download" onclick="downloadPDF()" disabled>⬇ Download PDF</button>
    </div>
</div>

<div class="app-body">
    <div class="chat-panel">
        <div class="chat-header">
            <h3>Cover Letter Coach</h3>
            <p>Answer a few questions to personalise your cover letter</p>
        </div>
        <div class="chat-messages" id="chat-messages"></div>
        <div class="chat-input-area">
            <div class="input-row">
                <textarea class="chat-input" id="user-input" placeholder="Type your answer here…" rows="1"
                    onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                <button class="send-btn" id="send-btn" onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <div class="preview-panel">
        <div class="preview-toolbar">
            <h3>Cover Letter Preview</h3>
            <div style="display:flex; gap:.4rem;">
                <button class="btn btn-outline" id="btn-regen" onclick="regenerate()" disabled style="font-size:.82rem;">↺ Regenerate</button>
                <button class="btn btn-accent" id="btn-dl2" onclick="downloadPDF()" disabled style="font-size:.82rem;">⬇ Download PDF</button>
            </div>
        </div>
        <div class="preview-scroll">
            <div id="doc-preview">
                <div class="cover-sheet">
                    <div class="cover-placeholder">
                        <div class="icon">✉️</div>
                        <h3>Your cover letter will appear here</h3>
                        <p>Answer the coach's questions on the left.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const SESSION_ID  = {{ $session->id }};
const COVER_ID    = {{ $cover->id }};
const COVER_DATA  = @json($coverData ?? null);
const IS_PRO      = {{ auth()->user()->isPro() ? 'true' : 'false' }};
const UPGRADE_URL = "{{ route('billing.upgrade') }}";
const API_URL     = "{{ url('/api/chat') }}";
const CSRF        = document.querySelector('meta[name="csrf-token"]').content;

let coverData = null;

window.addEventListener('DOMContentLoaded', async () => {
    try {
        const res  = await fetch(`/api/chat/session/${SESSION_ID}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();
        const history = data.messages ?? [];

        if (history.length > 0) {
            // Replay chat history
            history.forEach(m => {
                if (m.role === 'user' || m.role === 'assistant') {
                    addMsg(m.role === 'assistant' ? 'ai' : 'user', m.content);
                }
            });

            // Restore cover letter preview if already generated
            if (COVER_DATA && Object.keys(COVER_DATA).length > 0) {
                coverData = COVER_DATA;
                renderCover(coverData);
                enableDownload();
            } else {
                showPlaceholder();
            }

        } else {
            // Fresh session — show welcome
            showPlaceholder();
            const intro = {{ $resume ? 'true' : 'false' }}
                ? "Great — let's write your cover letter! 🎯\n\n**What company are you applying to**, and what specifically excites you about this role or company?"
                : "Let's write your cover letter! 🎯\n\nFirst, tell me: **What role and company are you applying for?**";
            addMsg('ai', intro);
        }

    } catch (e) {
        showPlaceholder();
        addMsg('ai', "Let's write your cover letter! 🎯\n\nWhat role and company are you applying for?");
    }
});

function showPlaceholder() {
    document.getElementById('doc-preview').innerHTML = `
        <div class="cover-sheet">
            <div class="cover-placeholder">
                <div class="icon">✉️</div>
                <h3>Your cover letter will appear here</h3>
                <p>Answer the coach's questions on the left.</p>
            </div>
        </div>`;
}

function enableDownload() {
    ['btn-download', 'btn-dl2', 'btn-regen'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = false;
    });
}

function addMsg(role, text) {
    const wrap = document.getElementById('chat-messages');
    const div  = document.createElement('div');
    div.className = `msg ${role}`;
    div.innerHTML = text.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>');
    wrap.appendChild(div);
    wrap.scrollTop = wrap.scrollHeight;
}

function showTyping() {
    const wrap = document.getElementById('chat-messages');
    const el = document.createElement('div');
    el.className = 'typing'; el.id = 'typing';
    el.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
    wrap.appendChild(el); wrap.scrollTop = wrap.scrollHeight;
}

function removeTyping() { document.getElementById('typing')?.remove(); }

async function sendMessage() {
    const input = document.getElementById('user-input');
    const text  = input.value.trim();
    if (!text) return;
    input.value = ''; autoResize(input);
    addMsg('user', text);
    document.getElementById('send-btn').disabled = true;
    showTyping();

    try {
        const res  = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ session_id: SESSION_ID, message: text })
        });
        const data = await res.json();
        removeTyping();

        if (res.status === 402) {
            addMsg('ai', `You've used all your free credits. <a href="${UPGRADE_URL}" style="color:var(--accent);font-weight:600;">Upgrade to Pro →</a>`);
        } else {
            addMsg('ai', data.message || 'Something went wrong.');
            if (data.cover_json) {
                renderCover(data.cover_json);
                ['btn-download','btn-dl2','btn-regen'].forEach(id => { const e = document.getElementById(id); if(e) e.disabled = false; });
                const cc = document.getElementById('credits-count');
                if (cc && !data.is_pro) cc.textContent = data.credits_left;
            }
        }
    } catch(e) {
        removeTyping();
        addMsg('ai', 'Connection error. Please try again.');
    }
    document.getElementById('send-btn').disabled = false;
}

function handleKey(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } }
function autoResize(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 110) + 'px'; }

function renderCover(d) {
    document.getElementById('doc-preview').innerHTML = `
        <div class="cover-sheet" id="printable">
            <p class="cl-date">${d.date||''}</p>
            <p style="margin-bottom:1.25rem;">Dear ${d.hiringManager||'Hiring Manager'},</p>
            <div class="cl-body">${(d.paragraphs||[]).map(p=>`<p>${p}</p>`).join('')}</div>
            <div class="cl-sign"><p>${d.signoff||'Best regards,'}</p><br><p><strong>${d.name||''}</strong></p></div>
        </div>`;
}

function downloadPDF() {
    const el = document.getElementById('printable');
    if (!el) { alert('Generate your cover letter first!'); return; }

    const name = (coverData?.name || 'Cover_Letter').replace(/\s+/g, '_');
    const btns = ['btn-download', 'btn-dl2'];
    btns.forEach(id => {
        const b = document.getElementById(id);
        if (b) { b.disabled = true; b.textContent = 'Generating…'; }
    });

    const clone = el.cloneNode(true);
    clone.style.cssText = 'background:white;border:none;box-shadow:none;border-radius:0;padding:0;margin:0;max-width:100%;';

    html2pdf().set({
        margin:      [15, 18, 15, 18],
        filename:    `${name}_Cover_Letter.pdf`,
        image:       { type: 'jpeg', quality: 1 },
        html2canvas: { scale: 3, useCORS: true, backgroundColor: '#ffffff' },
        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(clone).save().then(() => {
        btns.forEach(id => {
            const b = document.getElementById(id);
            if (b) { b.disabled = false; b.textContent = '⬇ Download PDF'; }
        });
    });
}

async function regenerate() {
    document.getElementById('user-input').value = 'Please regenerate my cover letter to be more compelling and specific.';
    await sendMessage();
}
</script>
</body>
</html>