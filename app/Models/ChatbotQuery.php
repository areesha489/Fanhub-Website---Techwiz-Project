<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotQuery extends Model
{
    protected $table = 'chatbot_queries';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'message',
        'response',
        'knowledge_id',
    ];

    /**
     * Member that asked the question, null for guests.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Knowledge entry that produced the answer, when one matched.
     */
    public function knowledge(): BelongsTo
    {
        return $this->belongsTo(ChatbotKnowledge::class, 'knowledge_id');
    }
}
