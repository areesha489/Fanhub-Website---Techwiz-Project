<?php

namespace App\Models;

use Database\Factories\CharacterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Character extends Model
{
    /** @use HasFactory<CharacterFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'characters';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'fandom',
        'image',
        'gallery',
        'bio',
        'gender',
        'age',
        'affiliation',
        'abilities',
        'debut',
        'views_count',
        'popularity',
        'featured',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gallery' => 'array',
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
     * Fandom bucket the character belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Titles the character appears in.
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_character', 'character_id', 'content_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Declared relationships (ally, rival, sibling…) to other characters.
     */
    public function relations(): HasMany
    {
        return $this->hasMany(CharacterRelation::class, 'character_id');
    }

    /**
     * Related characters joined with their relationship label.
     */
    public function relatedCharacters(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'character_relations',
            'character_id',
            'related_character_id'
        )->withPivot('relation');
    }

    /**
     * Reverse side: characters that declared a relationship towards this one.
     */
    public function relatedToCharacters(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'character_relations',
            'related_character_id',
            'character_id'
        )->withPivot('relation');
    }

    /**
     * Bookmarks pointing at the character.
     */
    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    /**
     * Members who favourited the character.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Member ratings for the character.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Characters promoted on the home page.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Match a free-text query against name, fandom and biography.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($term)).'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('name', 'like', $like)
                ->orWhere('fandom', 'like', $like)
                ->orWhere('bio', 'like', $like);
        });
    }
}
