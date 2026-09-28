<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'events';

    /**
     * Event formats supported by the calendar.
     *
     * @var list<string>
     */
    public const TYPES = ['Expo', 'Convention', 'Meetup', 'Tournament', 'Screening', 'Online', 'Workshop'];

    /**
     * Lifecycle states an event can be in.
     */
    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'event_type',
        'description',
        'cover_image',
        'start_date',
        'end_date',
        'time',
        'venue',
        'city',
        'country',
        'latitude',
        'longitude',
        'ticket_url',
        'status',
        'featured',
        'views_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'featured' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    /**
     * Resolve detail routes through the slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Fandom bucket the event belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Bookmarks pointing at the event.
     */
    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    /**
     * Members who favourited the event.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Member ratings for the event.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Events that have not finished yet.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UPCOMING);
    }

    /**
     * Events promoted on the home page.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Filter by lifecycle state.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Match a free-text query against name, venue and city.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($term)).'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('name', 'like', $like)
                ->orWhere('venue', 'like', $like)
                ->orWhere('city', 'like', $like);
        });
    }
}
