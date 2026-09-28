<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A single detail-page view, used to compute `views_count`.
 *
 * The table stores `created_at` only, so the model opts out of `updated_at`.
 */
class ContentView extends Model
{
    /**
     * Indicate that the table has no `updated_at` column.
     */
    public const UPDATED_AT = null;

    protected $table = 'views';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'viewable_type',
        'viewable_id',
        'ip',
    ];

    /**
     * Member that viewed the item, null for guests.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Item that was viewed.
     */
    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }
}
