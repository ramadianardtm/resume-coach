<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\Resume;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class CoverLetterController extends BaseController
{
    // ── List ───────────────────────────────────────────────────

    public function index(): \Illuminate\View\View
    {
        /** @var User $user */
        $user   = Auth::user();
        $covers = $user->coverLetters()->with('resume')->latest()->get();
        return view('cover.index', compact('covers'));
    }

    // ── GET /cover/create — handle <a href> links with ?resume_id= ──
    // The dashboard "+ Cover Letter" link is a GET <a href>, so we
    // handle it here and immediately POST-redirect into the builder.
    // This avoids the wildcard /{coverId} swallowing the string "create".

    public function createRedirect(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->canGenerate()) {
            return redirect()->route('billing.upgrade')
                ->with('error', 'You have used all your free credits. Upgrade to Pro to continue.');
        }

        $resumeId = (int) $request->query('resume_id', 0);
        $resume   = $resumeId ? Resume::where('id', $resumeId)->where('user_id', $user->id)->first() : null;

        // Create the cover letter record directly here (same logic as POST create)
        $cover = CoverLetter::create([
            'user_id'    => $user->id,
            'resume_id'  => $resume instanceof Resume ? $resume->id : null,
            'title'      => 'Cover Letter — ' . now()->format('M j, Y'),
            'status'     => 'draft',
            'cover_data' => [],
        ]);

        $session = ChatSession::create([
            'user_id'         => $user->id,
            'cover_letter_id' => $cover->id,
            'mode'            => 'cover_letter',
            'messages'        => [],
            'status'          => 'active',
        ]);

        return redirect()->route('cover.builder', [
            'coverId' => $cover->id,
            'session' => $session->id,
        ]);
    }

    // ── POST /cover/create ─────────────────────────────────────

    public function create(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->canGenerate()) {
            return redirect()->route('billing.upgrade')
                ->with('error', 'You have used all your free credits. Upgrade to Pro to continue.');
        }

        $resumeId = (int) $request->input('resume_id', 0);
        $resume   = $resumeId ? Resume::where('id', $resumeId)->where('user_id', $user->id)->first() : null;

        $cover = CoverLetter::create([
            'user_id'    => $user->id,
            'resume_id'  => $resume instanceof Resume ? $resume->id : null,
            'title'      => 'Cover Letter — ' . now()->format('M j, Y'),
            'status'     => 'draft',
            'cover_data' => [],
        ]);

        $session = ChatSession::create([
            'user_id'         => $user->id,
            'cover_letter_id' => $cover->id,
            'mode'            => 'cover_letter',
            'messages'        => [],
            'status'          => 'active',
        ]);

        return redirect()->route('cover.builder', [
            'coverId' => $cover->id,
            'session' => $session->id,
        ]);
    }

    // ── Builder view ───────────────────────────────────────────
    // Route param is always a string in Laravel — cast to int inside

    public function builder(string $coverId, Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        /** @var User $user */
        $user  = Auth::user();
        $cover = CoverLetter::where('id', (int) $coverId)->where('user_id', $user->id)->first();

        if (! $cover instanceof CoverLetter) {
            return redirect()->route('cover.index')->with('error', 'Cover letter not found.');
        }

        // Auto-find session by cover_letter_id (don't rely on query param)
        $session = ChatSession::where('cover_letter_id', $cover->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if (! $session instanceof ChatSession) {
            // Auto-create if missing
            $session = ChatSession::create([
                'user_id'         => $user->id,
                'cover_letter_id' => $cover->id,
                'mode'            => 'cover_letter',
                'messages'        => [],
                'status'          => 'active',
            ]);
        }

        $resume = $cover->resume;

        return view('cover.builder', [
            'cover'     => $cover,
            'session'   => $session,
            'resume'    => $resume,
            'coverData' => !empty($cover->cover_data) ? $cover->cover_data : null,  // ← add this
        ]);
    }

    // ── Show / Preview ─────────────────────────────────────────

    public function show(string $coverId): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        /** @var User $user */
        $user  = Auth::user();
        $cover = CoverLetter::where('id', (int) $coverId)->where('user_id', $user->id)->first();

        if (! $cover instanceof CoverLetter) {
            return redirect()->route('cover.index')->with('error', 'Cover letter not found.');
        }

        return view('cover.show', compact('cover'));
    }

    // ── Delete ─────────────────────────────────────────────────

    public function destroy(string $coverId): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user  = Auth::user();
        $cover = CoverLetter::where('id', (int) $coverId)->where('user_id', $user->id)->first();

        if (! $cover instanceof CoverLetter) {
            return redirect()->route('cover.index')->with('error', 'Cover letter not found.');
        }

        $cover->delete();
        return redirect()->route('dashboard')->with('success', 'Cover letter deleted.');
    }
}
