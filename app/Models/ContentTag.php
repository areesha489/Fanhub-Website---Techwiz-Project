<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot between `tags` and `contents`.
 *
 * The table uses a composite primary key (`tag_id`, `content_id`), so
 * attach/detach on the parent relations is preferred over stand-alone model
 * operations such as `find()` or `delete()`.
 */
class ContentTag extends Model
{
    protected $table = 'content_tags';

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
        'tag_id',
        'content_id',
    ];

    /**
     * Tag attached to the title.
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }

    /**
     * Title carrying the tag.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
