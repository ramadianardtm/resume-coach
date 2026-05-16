<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoverLetter extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'user_id',
        'resume_id',
        'title',
        'company_name',
        'hiring_manager',
        'cover_data',
        'raw_content',
        'status',
    ];
 
    protected $casts = [
        'cover_data' => 'array',
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
 
    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }
}
