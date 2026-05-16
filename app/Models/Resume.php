<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'user_id',
        'title',
        'target_role',
        'job_description',
        'resume_data',
        'raw_content',
        'status',
    ];
 
    protected $casts = [
        'resume_data' => 'array',
    ];
 
    // ── Relationships ──────────────────────────────────────────
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
 
    public function coverLetters()
    {
        return $this->hasMany(CoverLetter::class);
    }
 
    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }
 
    // ── Accessors ──────────────────────────────────────────────
 
    public function getApplicantNameAttribute(): string
    {
        return $this->resume_data['name'] ?? $this->user->name;
    }
 
    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }
}
