<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'image',
        'accent',
        'position',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * Series, films, games… filed under the category.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    /**
     * Characters filed under the category.
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Editorial pieces filed under the category.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Multimedia entries filed under the category.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Events filed under the category.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Merchandise filed under the category.
     */
    public function merchandise(): HasMany
    {
        return $this->hasMany(Merchandise::class);
    }

    /**
     * Upcoming releases filed under the category.
     */
    public function releases(): HasMany
    {
        return $this->hasMany(Release::class);
    }

    /**
     * Categories ordered for the sidebar navigation.
     *
     * The row payload is cached as a plain array (the database cache store
     * disables object unserialisation) and hydrated back into models.
     *
     * @return Collection<int, self>
     */
    public static function nav(): Collection
    {
        $rows = cache()->remember(
            'nav.categories',
            now()->addMinutes(10),
            fn (): array => static::query()
                ->whereIn('slug', ['anime', 'gaming', 'movies', 'tv-shows', 'k-pop', 'comics', 'manga', 'cosplay'])
                ->orderBy('position')
                ->get(['id', 'name', 'slug', 'icon', 'accent', 'position'])
                ->toArray()
        );

        return static::hydrate($rows);
    }
}
