<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot between `contents` and `characters`.
 *
 * The table uses a composite primary key (`content_id`, `character_id`), so
 * attach/detach on the parent relations is preferred over stand-alone model
 * operations such as `find()` or `delete()`.
 */
class ContentCharacter extends Model
{
    protected $table = 'content_character';

    /**
     * The primary key is a pair of foreign keys, not an auto-increment id.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'content_id',
        'character_id',
        'role',
    ];

    /**
     * Title the character appears in.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    /**
     * Character appearing in the title.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }
}
