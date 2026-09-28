<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Role granted to platform administrators.
     */
    public const ROLE_ADMIN = 'admin';

    /**
     * Role granted to regular members.
     */
    public const ROLE_USER = 'user';

    /**
     * Account status for members in good standing.
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * Account status for members blocked from signing in.
     */
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'role',
        'status',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine whether the member holds administrator rights.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Determine whether the account is allowed to use the platform.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Display and notification preferences for the member.
     */
    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Return the stored preferences, or an unsaved model pre-filled with
     * the documented defaults when none have been persisted yet.
     */
    public function resolvePreferences(): UserPreference
    {
        return $this->preferences ?? new UserPreference(array_merge(
            UserPreference::defaults(),
            ['user_id' => $this->id],
        ));
    }

    /**
     * Allow `$user->resolvePreferences` property style access as well.
     */
    protected function getResolvePreferencesAttribute(): UserPreference
    {
        return $this->resolvePreferences();
    }

    /**
     * Content, characters, events… saved for later reading.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Favourite fandoms (categories) and other entities saved by the member.
     *
     * The `favorites` table pairs `user_id` with a morph reference to the
     * favourited entity, so this is a plain one-to-many from the user side.
     *
     * @return HasMany<int, Favorite>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * In-app notifications issued to the member.
     *
     * Overrides the morph relation shipped with the Notifiable trait so the
     * custom `notifications` table is used.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Notifications that still need attention.
     */
    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Notifications already opened by the member.
     */
    public function readNotifications(): HasMany
    {
        return $this->notifications()->whereNotNull('read_at');
    }

    /**
     * Number of unread notifications, used by the topbar bell badge.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    /**
     * Audit trail of the member's notable actions.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Fan content awaiting (or having received) moderation.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FanSubmission::class);
    }

    /**
     * Articles written by the member.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /**
     * Members restricted to administrator review.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ADMIN);
    }
}
