<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterRelation extends Model
{
    protected $table = 'character_relations';

    /**
     * Supported relationship labels.
     *
     * @var list<string>
     */
    public const RELATIONS = ['ally', 'rival', 'sibling', 'mentor', 'love interest', 'enemy'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'character_id',
        'related_character_id',
        'relation',
    ];

    /**
     * Character that declared the relationship.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Character the relationship points at.
     */
    public function relatedCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'related_character_id');
    }
}
