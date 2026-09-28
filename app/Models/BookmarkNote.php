<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookmarkNote extends Model
{
    protected $table = 'bookmark_notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'bookmark_id',
        'note',
    ];

    /**
     * Bookmark the note history belongs to.
     */
    public function bookmark(): BelongsTo
    {
        return $this->belongsTo(Bookmark::class);
    }
}
