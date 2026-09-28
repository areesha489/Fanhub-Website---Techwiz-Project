<?php

namespace App\Models;

use Database\Factories\ChatbotKnowledgeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotKnowledge extends Model
{
    /** @use HasFactory<ChatbotKnowledgeFactory> */
    use HasFactory;

    protected $table = 'chatbot_knowledge';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'question',
        'answer',
        'keywords',
        'category',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Questions answered with this knowledge entry.
     */
    public function queries(): HasMany
    {
        return $this->hasMany(ChatbotQuery::class, 'knowledge_id');
    }

    /**
     * Limit to entries the assistant may answer with.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Match a free-text query against question, answer and keywords.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($term)).'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('question', 'like', $like)
                ->orWhere('answer', 'like', $like)
                ->orWhere('keywords', 'like', $like);
        });
    }
}
