@extends('layouts.app')

@section('title', 'My Resumes — ResumeCoach AI')

@push('styles')
<style>
    .resume-sheet { background:white; border:1px solid var(--border); border-radius:4px; max-width:740px; margin:0 auto; padding:3rem 3.5rem; box-shadow:0 4px 24px rgba(0,0,0,.06); font-size:.88rem; line-height:1.55; }
    .r-head { margin-bottom:1.5rem; border-bottom:2px solid var(--ink); padding-bottom:1rem; }
    .r-name { font-family:var(--serif); font-size:2rem; line-height:1.1; margin-bottom:.25rem; }
    .r-contact { font-size:.78rem; color:var(--muted); display:flex; gap:1rem; flex-wrap:wrap; }
    .r-section { margin-bottom:1.25rem; }
    .r-section-title { font-size:.74rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:var(--accent); margin-bottom:.6rem; border-bottom:1px solid var(--border); padding-bottom:.25rem; }
    .r-entry { margin-bottom:.85rem; }
    .r-entry-row { display:flex; justify-content:space-between; align-items:baseline; }
    .r-entry-title { font-weight:600; font-size:.9rem; }
    .r-entry-sub { color:var(--muted); font-size:.82rem; }
    .r-entry-date { font-size:.8rem; color:var(--muted); flex-shrink:0; margin-left:.5rem; }
    .r-entry ul { margin-top:.35rem; padding-left:1.1rem; }
    .r-entry ul li { font-size:.83rem; line-height:1.55; margin-bottom:.2rem; }
    .r-summary { font-size:.88rem; line-height:1.65; color:#333; }
    .skills-wrap { display:flex; flex-wrap:wrap; gap:.4rem; }
    .skill-tag { background:var(--cream); border:1px solid var(--border); border-radius:3px; padding:.2rem .5rem; font-size:.78rem; }
</style>
@endpush

@section('content')
<div class="page-content">

    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $resume->title }}</h1>
            <p class="page-subtitle">{{ $resume->target_role ? 'Target role: ' . $resume->target_role . ' · ' : '' }}Last updated {{ $resume->updated_at->diffForHumans() }}</p>
        </div>
        <div style="display:flex; gap:.75rem;">
            <a href="{{ route('cover.create', ['resume_id' => $resume->id]) }}" class="btn btn-outline">+ Cover Letter</a>
            <button onclick="printResume()" class="btn btn-primary">⬇ Download PDF</button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline">← Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php $d = $resume->resume_data; @endphp

    <div class="resume-sheet" id="resume-printable">
        <div class="r-head">
            <div class="r-name">{{ $d['name'] ?? $resume->user->name }}</div>
            <div class="r-contact">
                @if(!empty($d['email'])) <span>{{ $d['email'] }}</span> @endif
                @if(!empty($d['phone'])) <span>{{ $d['phone'] }}</span> @endif
                @if(!empty($d['location'])) <span>{{ $d['location'] }}</span> @endif
                @if(!empty($d['linkedin'])) <span>{{ $d['linkedin'] }}</span> @endif
            </div>
        </div>

        @if(!empty($d['summary']))
            <div class="r-section">
                <div class="r-section-title">Professional Summary</div>
                <p class="r-summary">{{ $d['summary'] }}</p>
            </div>
        @endif

        @if(!empty($d['experience']))
            <div class="r-section">
                <div class="r-section-title">Experience</div>
                @foreach($d['experience'] as $job)
                    <div class="r-entry">
                        <div class="r-entry-row">
                            <div>
                                <span class="r-entry-title">{{ $job['title'] ?? '' }}</span>
                                <span class="r-entry-sub"> · {{ $job['company'] ?? '' }}</span>
                            </div>
                            <span class="r-entry-date">{{ $job['duration'] ?? '' }}</span>
                        </div>
                        @if(!empty($job['bullets']))
                            <ul>@foreach($job['bullets'] as $b)<li>{{ $b }}</li>@endforeach</ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if(!empty($d['education']))
            <div class="r-section">
                <div class="r-section-title">Education</div>
                @foreach($d['education'] as $edu)
                    <div class="r-entry">
                        <div class="r-entry-row">
                            <div>
                                <span class="r-entry-title">{{ $edu['degree'] ?? '' }}</span>
                                <span class="r-entry-sub"> · {{ $edu['school'] ?? '' }}</span>
                            </div>
                            <span class="r-entry-date">{{ $edu['year'] ?? '' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if(!empty($d['skills']))
            <div class="r-section">
                <div class="r-section-title">Skills</div>
                <div class="skills-wrap">
                    @foreach($d['skills'] as $skill)
                        <span class="skill-tag">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function printResume() {
    const el     = document.getElementById('resume-printable');
    const styles = document.querySelectorAll('style, link[rel="stylesheet"]');
    let css = '';
    styles.forEach(s => { css += s.outerHTML; });
    const win = window.open('', '_blank');
    win.document.write(`<!DOCTYPE html><html><head><title>Resume</title>${css}<style>body{padding:2rem;}.resume-sheet{box-shadow:none!important;border:none!important;max-width:100%!important;}</style></head><body>${el.outerHTML}</body></html>`);
    win.document.close();
    setTimeout(() => win.print(), 500);
}
</script>
@endpush
@endsection