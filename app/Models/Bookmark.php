<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bookmark extends Model
{
    protected $table = 'bookmarks';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'bookmarkable_type',
        'bookmarkable_id',
        'note',
    ];

    /**
     * Member who saved the item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Saved item (content, character, article, media, event, merchandise…).
     */
    public function bookmarkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Note edit history, newest entry first.
     *
     * @return HasMany<int, BookmarkNote>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(BookmarkNote::class)->latest('id');
    }

    /**
     * Most recent entry of the note history.
     *
     * @return HasOne<BookmarkNote, $this>
     */
    public function latestNote(): HasOne
    {
        return $this->hasOne(BookmarkNote::class)->latestOfMany();
    }
}
