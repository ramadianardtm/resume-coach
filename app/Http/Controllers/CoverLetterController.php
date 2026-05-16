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
    public function index(): \Illuminate\View\View
    {
        /** @var User $user */
        $user   = Auth::user();
        $covers = $user->coverLetters()->with('resume')->latest()->get();
        return view('cover.index', compact('covers'));
    }

    public function create(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->canGenerate()) {
            return redirect()->route('billing.upgrade')
                ->with('error', 'You have used all your free credits. Upgrade to Pro to continue.');
        }

        $resumeId = (int) $request->query('resume_id', 0);
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

        return redirect()->route('cover.builder', ['cover' => $cover->id, 'session' => $session->id]);
    }

    public function builder(int $coverId, Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        /** @var User $user */
        $user  = Auth::user();
        $cover = CoverLetter::where('id', $coverId)->where('user_id', $user->id)->first();

        if (! $cover instanceof CoverLetter) {
            return redirect()->route('cover.index')->with('error', 'Cover letter not found.');
        }

        $session = ChatSession::where('id', (int) $request->query('session'))
            ->where('user_id', $user->id)
            ->first();

        if (! $session instanceof ChatSession) {
            return redirect()->route('cover.index')->with('error', 'Session not found.');
        }

        $resume = $cover->resume;

        return view('cover.builder', compact('cover', 'session', 'resume'));
    }

    public function show(int $coverId): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        /** @var User $user */
        $user  = Auth::user();
        $cover = CoverLetter::where('id', $coverId)->where('user_id', $user->id)->first();

        if (! $cover instanceof CoverLetter) {
            return redirect()->route('cover.index')->with('error', 'Cover letter not found.');
        }

        return view('cover.show', compact('cover'));
    }

    public function destroy(int $coverId): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user  = Auth::user();
        $cover = CoverLetter::where('id', $coverId)->where('user_id', $user->id)->first();

        if (! $cover instanceof CoverLetter) {
            return redirect()->route('cover.index')->with('error', 'Cover letter not found.');
        }

        $cover->delete();
        return redirect()->route('cover.index')->with('success', 'Cover letter deleted.');
    }
}