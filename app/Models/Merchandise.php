<?php

namespace App\Models;

use Database\Factories\MerchandiseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchandise extends Model
{
    /** @use HasFactory<MerchandiseFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'merchandise';

    /**
     * Editorial states a product can be in.
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'fandom',
        'description',
        'image',
        'price',
        'currency',
        'tags',
        'is_upcoming',
        'release_date',
        'popularity',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'tags' => 'array',
            'is_upcoming' => 'boolean',
            'release_date' => 'date',
            'popularity' => 'integer',
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
     * The public contract exposes the product name; it is stored as `title`.
     */
    public function getNameAttribute(): string
    {
        return (string) $this->title;
    }

    /**
     * Fandom bucket the product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Bookmarks pointing at the product.
     */
    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    /**
     * Members who favourited the product.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Member ratings for the product.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Limit to publicly released products.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
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
     * Match a free-text query against title, fandom and description.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($term)).'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('title', 'like', $like)
                ->orWhere('fandom', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
