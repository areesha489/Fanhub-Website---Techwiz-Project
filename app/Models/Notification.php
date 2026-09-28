<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'link',
        'read_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * Member the notification belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the notification as read when it is still unread.
     */
    public function markAsRead(): bool
    {
        if ($this->read_at !== null) {
            return false;
        }

        return (bool) $this->forceFill(['read_at' => now()])->save();
    }

    /**
     * Determine whether the notification has been opened.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
