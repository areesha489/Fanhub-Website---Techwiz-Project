<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Audit trail entry for notable member actions.
 *
 * The table stores `created_at` only, so the model opts out of `updated_at`.
 */
class ActivityLog extends Model
{
    /**
     * Indicate that the table has no `updated_at` column.
     */
    public const UPDATED_AT = null;

    protected $table = 'activity_logs';

    /**
     * Actions recorded by the platform.
     *
     * @var list<string>
     */
    public const ACTIONS = ['login', 'logout', 'bookmark_added', 'bookmark_removed', 'submission', 'rating', 'profile_updated'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'ip_address',
    ];

    /**
     * Member the activity belongs to, null for guests.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Entity the activity refers to.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
