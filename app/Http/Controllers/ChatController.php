<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\CoverLetter;
use App\Models\Resume;
use App\Models\User;
use App\Services\ClaudeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class ChatController extends BaseController
{
    public function __construct(private ClaudeService $claude)
    {
    }

    /**
     * POST /api/chat
     * Accepts a message, appends to session history, calls Claude, saves result.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => ['required', 'integer'],
            'message'    => ['required', 'string', 'max:4000'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Explicit ownership query — avoids IDE confusion over firstOrFail() generics
        $session = ChatSession::where('id', (int) $request->session_id)
            ->where('user_id', $user->id)
            ->first();

        if (! $session instanceof ChatSession) {
            return response()->json(['error' => 'Session not found.'], 404);
        }

        // Guard: check credits
        if (! $user->canGenerate()) {
            return response()->json([
                'error'       => 'no_credits',
                'message'     => 'You have used all your free credits.',
                'upgrade_url' => route('billing.upgrade'),
            ], 402);
        }

        // Append user message
        $session->appendMessage('user', $request->message);

        // Build messages for Claude (last 10 for context window efficiency)
        $messages = $session->lastMessages(10);

        // Pick system prompt based on mode
        $systemPrompt = $session->mode === 'resume'
            ? $this->claude->resumeSystemPrompt()
            : $this->buildCoverSystemPrompt($session);

        // Call Claude
        $result = $this->claude->chat($messages, $systemPrompt);

        // Append AI response
        $session->appendMessage('assistant', $result['text']);

        $response = ['message' => $result['text']];

        // Handle resume generation
        if (! empty($result['resume_json'])) {
            $resume = Resume::find((int) $session->resume_id);
            if ($resume instanceof Resume) {
                $resume->update([
                    'resume_data' => $result['resume_json'],
                    'target_role' => $result['resume_json']['targetRole'] ?? null,
                    'title'       => ($result['resume_json']['targetRole'] ?? 'Resume') . ' — ' . now()->format('M j, Y'),
                    'status'      => 'complete',
                ]);
            }
            $user->deductCredit();
            $freshUser                  = $user->fresh();
            $response['resume_json']    = $result['resume_json'];
            $response['credits_left']   = $freshUser instanceof User ? $freshUser->credits : 0;
            $response['is_pro']         = $user->isPro();
        }

        // Handle cover letter generation
        if (! empty($result['cover_json'])) {
            $cover = CoverLetter::find((int) $session->cover_letter_id);
            if ($cover instanceof CoverLetter) {
                $cover->update([
                    'cover_data'     => $result['cover_json'],
                    'company_name'   => $result['cover_json']['company'] ?? null,
                    'hiring_manager' => $result['cover_json']['hiringManager'] ?? null,
                    'title'          => 'Cover Letter — ' . ($result['cover_json']['company'] ?? now()->format('M j, Y')),
                    'status'         => 'complete',
                ]);
            }
            $user->deductCredit();
            $freshUser                 = $user->fresh();
            $response['cover_json']    = $result['cover_json'];
            $response['credits_left']  = $freshUser instanceof User ? $freshUser->credits : 0;
            $response['is_pro']        = $user->isPro();
        }

        return response()->json($response);
    }

    /**
     * GET /api/chat/session/{session}
     * Returns full session history (for page-reload recovery).
     * Ownership enforced manually — no Policy needed.
     */
    public function history(int $sessionId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (! $session instanceof ChatSession) {
            return response()->json(['error' => 'Session not found.'], 404);
        }

        return response()->json([
            'messages' => $session->messages,
            'status'   => $session->status,
        ]);
    }

    // ── Private helpers ────────────────────────────────────────

    private function buildCoverSystemPrompt(ChatSession $session): string
    {
        $base = $this->claude->coverLetterSystemPrompt();

        if ($session->cover_letter_id) {
            $cover  = CoverLetter::find((int) $session->cover_letter_id);
            $resume = $cover?->resume;
            if ($resume instanceof Resume && ! empty($resume->resume_data)) {
                $base = "The user's resume data: " . json_encode($resume->resume_data) . "\n\n" . $base;
            }
        }

        return $base;
    }
}