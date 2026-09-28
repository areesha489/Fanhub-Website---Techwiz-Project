<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $table = 'user_preferences';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'theme',
        'font_scale',
        'reduced_motion',
        'email_notifications',
        'content_notifications',
        'event_notifications',
        'newsletter',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'font_scale' => 'decimal:2',
            'reduced_motion' => 'boolean',
            'email_notifications' => 'boolean',
            'content_notifications' => 'boolean',
            'event_notifications' => 'boolean',
            'newsletter' => 'boolean',
        ];
    }

    /**
     * Defaults applied when a member has no stored preferences yet.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'theme' => 'dark',
            'font_scale' => 1,
            'reduced_motion' => false,
            'email_notifications' => true,
            'content_notifications' => true,
            'event_notifications' => true,
            'newsletter' => false,
        ];
    }

    /**
     * Member the preferences belong to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
