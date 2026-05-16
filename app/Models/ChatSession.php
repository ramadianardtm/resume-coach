<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'user_id',
        'resume_id',
        'cover_letter_id',
        'mode',
        'messages',
        'status',
    ];
 
    protected $casts = [
        'messages' => 'array',
    ];
 
    // ── Relationships ──────────────────────────────────────────
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
 
    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
 
    public function coverLetter()
    {
        return $this->belongsTo(CoverLetter::class);
    }
 
    // ── Helpers ────────────────────────────────────────────────
 
    public function appendMessage(string $role, string $content): void
    {
        $messages = $this->messages ?? [];
        $messages[] = ['role' => $role, 'content' => $content];
        $this->update(['messages' => $messages]);
    }
 
    public function lastMessages(int $count = 10): array
    {
        $messages = $this->messages ?? [];
        return array_slice($messages, -$count);
    }
}
