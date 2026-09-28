<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    protected $table = 'favorites';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'favoritable_type',
        'favoritable_id',
    ];

    /**
     * Member that favourited the item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Favourited item (typically a category acting as a favourite fandom).
     */
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }
}
