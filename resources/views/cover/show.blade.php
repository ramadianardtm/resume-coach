@extends('layouts.app')

@section('title', $cover->title . ' — ResumeCoach AI')

@push('styles')
    <style>
        .cover-sheet {
            background: white;
            border: 1px solid var(--border);
            border-radius: 4px;
            max-width: 720px;
            margin: 0 auto;
            padding: 3rem 3.5rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .06);
            font-size: .9rem;
            line-height: 1.75;
        }

        .cl-date {
            color: var(--muted);
            margin-bottom: 1.5rem;
            font-size: .855rem;
        }

        .cl-body p {
            margin-bottom: 1rem;
        }

        .cl-sign {
            margin-top: 2rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $cover->title }}</h1>
                <p class="page-subtitle">
                    {{ $cover->company_name ? 'For: ' . $cover->company_name . ' · ' : '' }}
                    Last updated {{ $cover->updated_at->diffForHumans() }}
                </p>
            </div>
            <div style="display:flex; gap:.75rem;">
                <button onclick="printCover()" class="btn btn-primary">⬇ Download PDF</button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline">← Dashboard</a>
            </div>
        </div>

        @php $d = $cover->cover_data; @endphp

        <div class="cover-sheet" id="cover-printable">
            <p class="cl-date">{{ $d['date'] ?? '' }}</p>
            <p style="margin-bottom:1.5rem;">Dear {{ $d['hiringManager'] ?? 'Hiring Manager' }},</p>
            <div class="cl-body">
                @foreach ($d['paragraphs'] ?? [] as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>
            <div class="cl-sign">
                <p>{{ $d['signoff'] ?? 'Best regards,' }}</p>
                <br>
                <p><strong>{{ $d['name'] ?? auth()->user()->name }}</strong></p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script>
            function printCover() {
                const el = document.getElementById('cover-printable');
                if (!el) {
                    alert('Nothing to download!');
                    return;
                }

                const btn = document.querySelector('[onclick="printCover()"]');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Generating…';
                }

                const clone = el.cloneNode(true);
                clone.style.cssText =
                    'background:white;border:none;box-shadow:none;border-radius:0;padding:0;margin:0;max-width:100%;';

                html2pdf().set({
                    margin: [15, 18, 15, 18],
                    filename: 'Cover_Letter.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 1
                    },
                    html2canvas: {
                        scale: 3,
                        useCORS: true,
                        backgroundColor: '#ffffff'
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'portrait'
                    }
                }).from(clone).save().then(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = '⬇ Download PDF';
                    }
                });
            }
        </script>
    @endpush
@endsection
