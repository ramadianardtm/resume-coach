<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class ResumeController extends BaseController
{
    // ── List ───────────────────────────────────────────────────

    public function index(): \Illuminate\View\View
    {
        /** @var User $user */
        $user    = Auth::user();
        $resumes = $user->resumes()->latest()->get();
        return view('resume.index', compact('resumes'));
    }

    // ── Start a new resume (creates session) ───────────────────

    public function create(): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->canGenerate()) {
            return redirect()->route('billing.upgrade')
                ->with('error', 'You have used all your free credits. Upgrade to Pro to continue.');
        }

        $resume = Resume::create([
            'user_id'     => $user->id,
            'title'       => 'New Resume — ' . now()->format('M j, Y'),
            'status'      => 'draft',
            'resume_data' => [],
        ]);

        $session = ChatSession::create([
            'user_id'   => $user->id,
            'resume_id' => $resume->id,
            'mode'      => 'resume',
            'messages'  => [],
            'status'    => 'active',
        ]);

        return redirect()->route('resume.builder', ['resume' => $resume->id, 'session' => $session->id]);
    }

    // ── Builder view ───────────────────────────────────────────

    public function builder(int $resumeId, Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        /** @var User $user */
        $user   = Auth::user();
        $resume = Resume::where('id', $resumeId)->where('user_id', $user->id)->first();

        if (! $resume instanceof Resume) {
            return redirect()->route('resume.index')->with('error', 'Resume not found.');
        }

        $session = ChatSession::where('id', (int) $request->query('session'))
            ->where('user_id', $user->id)
            ->first();

        if (! $session instanceof ChatSession) {
            return redirect()->route('resume.index')->with('error', 'Session not found.');
        }

        return view('resume.builder', compact('resume', 'session'));
    }

    // ── View / Preview ─────────────────────────────────────────

    public function show(int $resumeId): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        /** @var User $user */
        $user   = Auth::user();
        $resume = Resume::where('id', $resumeId)->where('user_id', $user->id)->first();

        if (! $resume instanceof Resume) {
            return redirect()->route('resume.index')->with('error', 'Resume not found.');
        }

        return view('resume.show', compact('resume'));
    }

    // ── Update title ───────────────────────────────────────────

    public function update(Request $request, int $resumeId): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user   = Auth::user();
        $resume = Resume::where('id', $resumeId)->where('user_id', $user->id)->first();

        if (! $resume instanceof Resume) {
            return redirect()->route('resume.index')->with('error', 'Resume not found.');
        }

        $request->validate(['title' => ['required', 'string', 'max:255']]);
        $resume->update(['title' => $request->title]);

        return back()->with('success', 'Title updated.');
    }

    // ── Delete ─────────────────────────────────────────────────

    public function destroy(int $resumeId): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user   = Auth::user();
        $resume = Resume::where('id', $resumeId)->where('user_id', $user->id)->first();

        if (! $resume instanceof Resume) {
            return redirect()->route('resume.index')->with('error', 'Resume not found.');
        }

        $resume->delete();
        return redirect()->route('resume.index')->with('success', 'Resume deleted.');
    }
}