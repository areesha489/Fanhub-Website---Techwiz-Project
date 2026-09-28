<?php

namespace App\Models;

use Database\Factories\ReleaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Release extends Model
{
    /** @use HasFactory<ReleaseFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'releases';

    /**
     * Release formats supported by the calendar.
     *
     * @var list<string>
     */
    public const TYPES = ['anime', 'game', 'movie', 'tv', 'comic', 'merchandise'];

    /**
     * Lifecycle states a release can be in.
     */
    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_RELEASED = 'released';

    public const STATUS_DELAYED = 'delayed';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'release_type',
        'category_id',
        'release_date',
        'description',
        'poster',
        'status',
        'content_id',
        'popularity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'popularity' => 'integer',
        ];
    }

    /**
     * Fandom bucket the release belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Catalogue title the release belongs to.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Bookmarks pointing at the release.
     */
    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    /**
     * Members who favourited the release.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Member ratings for the release.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Releases still waiting for their launch date.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UPCOMING);
    }

    /**
     * Releases already available.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeReleased(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RELEASED);
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
     * Match a free-text query against title and description.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($term)).'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('title', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
