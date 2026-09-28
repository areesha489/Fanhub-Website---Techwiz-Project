<?php

namespace App\Models;

use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'contents';

    /**
     * Editorial states a title can be in.
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * Content formats supported by the catalogue.
     *
     * @var list<string>
     */
    public const TYPES = ['Series', 'Movie', 'Game', 'TV Show', 'Album', 'Chapter', 'Other'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'content_type',
        'genre',
        'description',
        'synopsis',
        'cover_image',
        'banner_image',
        'release_date',
        'status',
        'rating',
        'views_count',
        'popularity',
        'featured',
        'trailer_url',
        'created_by',
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
            'rating' => 'decimal:1',
            'views_count' => 'integer',
            'popularity' => 'integer',
            'featured' => 'boolean',
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
     * Fandom bucket the title belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Member that contributed the record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Labels attached to the title.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_tags', 'content_id', 'tag_id')->withTimestamps();
    }

    /**
     * Characters appearing in the title.
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'content_character', 'content_id', 'character_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Bookmarks pointing at the title.
     */
    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    /**
     * Members who favourited the title.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Member ratings for the title.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Limit to publicly released titles.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Limit to titles promoted on the home page.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Filter by publication state.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Match a free-text query against title, genre and description.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($term)).'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('title', 'like', $like)
                ->orWhere('genre', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
