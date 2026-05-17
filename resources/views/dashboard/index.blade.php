@extends('layouts.app')

@section('title', 'Dashboard — ResumeCoach AI')

@push('styles')
<style>
    @keyframes slideUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="page-content">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Good to see you, {{ explode(' ', $user->name)[0] }} 👋</h1>
            <p class="page-subtitle">
                @if ($user->isPro())
                    You're on <strong>Pro</strong> — unlimited generations.
                @else
                    You have <strong>{{ $user->credits }}</strong> free
                    generation{{ $user->credits !== 1 ? 's' : '' }} remaining.
                    <a href="{{ route('billing.upgrade') }}" style="color:var(--accent); font-weight:500;">
                        Upgrade for unlimited →
                    </a>
                @endif
            </p>
        </div>
        <div style="display:flex; gap:.75rem; flex-wrap:wrap;">
            <a href="{{ route('cover.create') }}" class="btn btn-outline">+ Cover Letter</a>
            <form action="{{ route('resume.create') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">+ New Resume</button>
            </form>
        </div>
    </div>

    {{-- Resumes --}}
    <div style="margin-bottom:2.5rem;">
        <h2 style="font-size:.8rem; font-weight:600; margin-bottom:1rem; color:var(--muted); text-transform:uppercase; letter-spacing:.06em;">
            Resumes
        </h2>

        @if ($resumes->isEmpty())
            <div class="card" style="padding:3rem 2rem; text-align:center; border-radius:8px;">
                <div style="font-size:2.5rem; margin-bottom:.75rem; opacity:.4;">📋</div>
                <h3 style="font-family:var(--serif); font-size:1.4rem; margin-bottom:.5rem;">No resumes yet</h3>
                <p style="color:var(--muted); font-size:.9rem; margin-bottom:1.5rem;">
                    Start your first AI coaching session and your resume will be built from the conversation.
                </p>
                <form action="{{ route('resume.create') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Build my first resume →</button>
                </form>
            </div>
        @else
            <div class="card-grid">
                @foreach ($resumes as $resume)
                    <div class="doc-card">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:.3rem;">
                            <div class="doc-card-title">{{ $resume->title }}</div>
                            <span class="status-badge {{ $resume->status === 'complete' ? 'status-complete' : 'status-draft' }}">
                                {{ $resume->status }}
                            </span>
                        </div>
                        <div class="doc-card-meta">
                            {{ $resume->target_role ? 'For: ' . $resume->target_role . ' · ' : '' }}
                            Updated {{ $resume->updated_at->diffForHumans() }}
                        </div>
                        <div class="doc-card-actions">
                            @if ($resume->status === 'complete')
                                <a href="{{ route('resume.show', $resume->id) }}" class="btn btn-outline btn-sm">View</a>
                                <a href="{{ route('cover.create', ['resume_id' => $resume->id]) }}" class="btn btn-outline btn-sm">+ Cover Letter</a>
                            @else
                                @if ($resume->latestSession)
                                    <a href="{{ route('resume.builder', ['resumeId' => $resume->id, 'session' => $resume->latestSession->id]) }}"
                                       class="btn btn-outline btn-sm">Continue →</a>
                                @else
                                    <form action="{{ route('resume.create') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm">Restart</button>
                                    </form>
                                @endif
                            @endif
                            <button type="button" class="btn btn-danger btn-sm"
                                onclick="openDeleteModal('resume', {{ $resume->id }}, '{{ addslashes($resume->title) }}')">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Cover Letters --}}
    <div>
        <h2 style="font-size:.8rem; font-weight:600; margin-bottom:1rem; color:var(--muted); text-transform:uppercase; letter-spacing:.06em;">
            Cover Letters
        </h2>

        @if ($covers->isEmpty())
            <div class="card" style="color:var(--muted); font-size:.9rem; padding:1.25rem; border-radius:8px;">
                No cover letters yet. Complete a resume first, then generate a tailored cover letter for each application.
            </div>
        @else
            <div class="card-grid">
                @foreach ($covers as $cover)
                    <div class="doc-card">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:.3rem;">
                            <div class="doc-card-title">{{ $cover->title }}</div>
                            <span class="status-badge {{ $cover->status === 'complete' ? 'status-complete' : 'status-draft' }}">
                                {{ $cover->status }}
                            </span>
                        </div>
                        <div class="doc-card-meta">
                            {{ $cover->company_name ? 'For: ' . $cover->company_name . ' · ' : '' }}
                            Updated {{ $cover->updated_at->diffForHumans() }}
                        </div>
                        <div class="doc-card-actions">
                            @if ($cover->status === 'complete')
                                <a href="{{ route('cover.show', $cover->id) }}" class="btn btn-outline btn-sm">View</a>
                            @endif
                            <button type="button" class="btn btn-danger btn-sm"
                                onclick="openDeleteModal('cover', {{ $cover->id }}, '{{ addslashes($cover->title) }}')">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ── Custom Delete Modal ── --}}
<div id="delete-modal"
     style="display:none; position:fixed; inset:0; z-index:999; align-items:center; justify-content:center; padding:1rem;">
    <div onclick="closeDeleteModal()"
         style="position:absolute; inset:0; background:rgba(15,14,12,.55); backdrop-filter:blur(3px);"></div>
    <div style="position:relative; background:white; border-radius:12px; padding:2.25rem 2rem; max-width:400px; width:100%; box-shadow:0 24px 64px rgba(15,14,12,.2); animation:slideUp .2s ease; text-align:center;">
        <div style="width:52px; height:52px; background:#fdf0ed; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto .75rem; font-size:1.4rem;">🗑️</div>
        <h3 style="font-family:var(--serif); font-size:1.3rem; margin-bottom:.5rem;">Delete this document?</h3>
        <p id="delete-modal-msg" style="color:var(--muted); font-size:.875rem; margin-bottom:1.75rem; line-height:1.6;"></p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:.65rem;">
            <button onclick="closeDeleteModal()"
                    style="padding:.75rem; border-radius:var(--radius); border:1px solid var(--border); background:transparent; font-family:var(--sans); font-size:.875rem; cursor:pointer; color:var(--muted); transition:all .15s;"
                    onmouseover="this.style.borderColor='var(--ink)';this.style.color='var(--ink)'"
                    onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
                Cancel
            </button>
            <button id="delete-confirm-btn"
                    style="padding:.75rem; border-radius:var(--radius); border:none; background:var(--accent); color:white; font-family:var(--sans); font-size:.875rem; font-weight:600; cursor:pointer; transition:background .15s;"
                    onmouseover="this.style.background='#b03825'"
                    onmouseout="this.style.background='var(--accent)'">
                Yes, delete it
            </button>
        </div>
    </div>
</div>

{{-- Hidden delete forms (CSRF-safe) --}}
<div style="display:none;">
    @foreach ($resumes as $resume)
        <form id="delete-resume-{{ $resume->id }}" action="{{ route('resume.destroy', $resume->id) }}" method="POST">
            @csrf @method('DELETE')
        </form>
    @endforeach
    @foreach ($covers as $cover)
        <form id="delete-cover-{{ $cover->id }}" action="{{ route('cover.destroy', $cover->id) }}" method="POST">
            @csrf @method('DELETE')
        </form>
    @endforeach
</div>

@push('scripts')
<script>
let pendingFormId = null;

function openDeleteModal(type, id, title) {
    pendingFormId = `delete-${type}-${id}`;
    document.getElementById('delete-modal-msg').textContent =
        `"${title}" will be permanently deleted and cannot be recovered.`;
    const modal = document.getElementById('delete-modal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('delete-modal').style.display = 'none';
    document.body.style.overflow = '';
    pendingFormId = null;
}

document.getElementById('delete-confirm-btn').addEventListener('click', () => {
    if (pendingFormId) document.getElementById(pendingFormId)?.submit();
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
</script>
@endpush

@endsection