@extends('layouts.app')

@section('title', 'My Cover Letters — ResumeCoach AI')

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Cover Letters</h1>
            <p class="page-subtitle">{{ $covers->count() }} cover letter{{ $covers->count() !== 1 ? 's' : '' }} saved</p>
        </div>
        <a href="{{ route('cover.create') }}" class="btn btn-primary">+ New Cover Letter</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($covers->isEmpty())
        <div class="card" style="padding:3rem 2rem; text-align:center; border-radius:8px;">
            <div style="font-size:2.5rem; margin-bottom:.75rem; opacity:.4;">✉️</div>
            <h3 style="font-family:var(--serif); font-size:1.4rem; margin-bottom:.5rem;">No cover letters yet</h3>
            <p style="color:var(--muted); font-size:.9rem; margin-bottom:1.5rem;">
                Complete a resume first, then generate a cover letter tailored to each job.
            </p>
            <a href="{{ route('resume.index') }}" class="btn btn-primary">Go to my resumes →</a>
        </div>
    @else
        <div class="card-grid">
            @foreach($covers as $cover)
                <div class="doc-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.25rem;">
                        <div class="doc-card-title">{{ $cover->title }}</div>
                        <span class="status-badge {{ $cover->status === 'complete' ? 'status-complete' : 'status-draft' }}">
                            {{ $cover->status }}
                        </span>
                    </div>
                    <div class="doc-card-meta">
                        {{ $cover->company_name ? 'For: ' . $cover->company_name . ' · ' : '' }}
                        {{ $cover->updated_at->diffForHumans() }}
                    </div>
                    <div class="doc-card-actions">
                        @if($cover->status === 'complete')
                            <a href="{{ route('cover.show', $cover->id) }}" class="btn btn-outline btn-sm">View</a>
                        @endif
                        <form action="{{ route('cover.destroy', $cover->id) }}" method="POST"
                              onsubmit="return confirm('Delete this cover letter?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection