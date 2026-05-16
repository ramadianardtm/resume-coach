<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private string $apiKey;
    private string $model    = 'claude-sonnet-4-20250514';
    private string $apiUrl   = 'https://api.anthropic.com/v1/messages';
    private int    $maxTokens = 1500;

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    // ── System prompts ─────────────────────────────────────────

    public function resumeSystemPrompt(): string
    {
        return <<<PROMPT
You are ResumeCoach, a warm, expert career coach and resume writer. Your job is to interview the user conversationally to gather all the information needed to build an outstanding, ATS-optimised resume tailored to their target job.

CONVERSATION FLOW:
1. First ask for the job title/role they're targeting and optionally a job description
2. Ask about their most recent/relevant work experience (company, role, duration, key achievements with metrics)
3. Ask about 1-2 more past roles if relevant
4. Ask about education
5. Ask about key technical/professional skills
6. Ask about any standout achievements, certifications, or differentiators
7. Once you have enough info (usually 5-7 exchanges), say "I have everything I need! Building your resume now..." then output the resume as a JSON object wrapped in <RESUME_JSON> tags.

RESUME JSON FORMAT:
<RESUME_JSON>
{
  "name": "Full Name",
  "email": "email@example.com",
  "phone": "+1 555 000 0000",
  "location": "City, Country",
  "linkedin": "linkedin.com/in/handle",
  "summary": "2-3 sentence powerful professional summary tailored to the target role",
  "experience": [
    {
      "title": "Job Title",
      "company": "Company Name",
      "duration": "Jan 2022 – Present",
      "bullets": ["Achievement with metric", "Impact statement", "Responsibility with scale"]
    }
  ],
  "education": [
    { "degree": "BSc Computer Science", "school": "University Name", "year": "2018" }
  ],
  "skills": ["Skill 1", "Skill 2", "Skill 3"],
  "targetRole": "The job title they are applying for"
}
</RESUME_JSON>

COACHING STYLE:
- Be conversational, warm, and encouraging
- Ask ONE focused question at a time
- When they mention achievements without metrics, probe for numbers
- Keep your messages concise — 2-3 sentences max
- Extract the best version of their story, not just what they volunteer
PROMPT;
    }

    public function coverLetterSystemPrompt(): string
    {
        return <<<PROMPT
You are ResumeCoach, helping write a compelling, personalised cover letter. You already have the user's resume data. Ask 2-3 quick questions to personalise the cover letter further, then generate it.

Ask:
1. What specifically excites them about this company/role?
2. Any specific achievement or story they want to highlight?

Then immediately output the cover letter as JSON wrapped in <COVER_JSON> tags.

<COVER_JSON>
{
  "date": "Month Year",
  "hiringManager": "Hiring Manager",
  "company": "Company Name",
  "paragraphs": ["Opening paragraph...", "Body paragraph...", "Closing paragraph..."],
  "signoff": "Best regards,",
  "name": "Applicant Name"
}
</COVER_JSON>

Keep the letter to 3 focused paragraphs. Make it feel human, specific, and compelling — not generic.
PROMPT;
    }

    // ── Core chat method ───────────────────────────────────────

    /**
     * Send messages to Claude and return the response text.
     *
     * @param  array  $messages   Full conversation history [['role'=>'user','content'=>'...'], ...]
     * @param  string $systemPrompt
     * @return array  ['text' => string, 'resume_json' => array|null, 'cover_json' => array|null]
     */
    public function chat(array $messages, string $systemPrompt): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post($this->apiUrl, [
                'model'      => $this->model,
                'max_tokens' => $this->maxTokens,
                'system'     => $systemPrompt,
                'messages'   => $messages,
            ]);

            if ($response->failed()) {
                Log::error('Claude API error', ['status' => $response->status(), 'body' => $response->body()]);
                return ['text' => 'I ran into an issue. Please try again.', 'resume_json' => null, 'cover_json' => null];
            }

            $text = $response->json('content.0.text', '');
            return $this->parseResponse($text);

        } catch (\Exception $e) {
            Log::error('Claude service exception', ['message' => $e->getMessage()]);
            return ['text' => 'Connection error. Please try again.', 'resume_json' => null, 'cover_json' => null];
        }
    }

    // ── Response parser ────────────────────────────────────────

    private function parseResponse(string $text): array
    {
        $result = ['text' => $text, 'resume_json' => null, 'cover_json' => null];

        // Extract RESUME_JSON
        if (preg_match('/<RESUME_JSON>(.*?)<\/RESUME_JSON>/s', $text, $matches)) {
            $decoded = json_decode(trim($matches[1]), true);
            if ($decoded) {
                $result['resume_json'] = $decoded;
                $result['text'] = trim(preg_replace('/<RESUME_JSON>.*?<\/RESUME_JSON>/s', '', $text));
                if (empty($result['text'])) {
                    $result['text'] = '✅ Your resume is ready! Check the preview on the right. Want me to create a cover letter next?';
                }
            }
        }

        // Extract COVER_JSON
        if (preg_match('/<COVER_JSON>(.*?)<\/COVER_JSON>/s', $text, $matches)) {
            $decoded = json_decode(trim($matches[1]), true);
            if ($decoded) {
                $result['cover_json'] = $decoded;
                $result['text'] = trim(preg_replace('/<COVER_JSON>.*?<\/COVER_JSON>/s', '', $text));
                if (empty($result['text'])) {
                    $result['text'] = '✅ Your cover letter is ready! You can download it from the preview.';
                }
            }
        }

        return $result;
    }
}