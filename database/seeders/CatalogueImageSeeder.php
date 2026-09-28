<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Release;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Points catalogue rows at the cinematic stills in public/images/catalogue.
 */
class CatalogueImageSeeder extends Seeder
{
    public function run(): void
    {
        $this->contents();
        $this->characters();
        $this->articles();
        $this->merchandise();
        $this->releases();
        $this->categories();
        $this->fillGaps();
        $this->spreadDuplicates();
    }

    /**
     * Wide banners used behind category and listing heroes.
     *
     * @return array<string, string>
     */
    private function banners(): array
    {
        return [
            'anime' => 'banner-anime.png',
            'gaming' => 'banner-gaming.png',
            'movies' => 'banner-movies.png',
            'tv-shows' => 'banner-tv.png',
            'k-pop' => 'banner-kpop.png',
            'comics' => 'banner-comics.png',
            'manga' => 'one-piece-banner.png',
            'cosplay' => 'banner-events.png',
        ];
    }

    /**
     * Poster pools so neighbouring cards are not identical.
     *
     * @return array<string, list<string>>
     */
    private function pools(): array
    {
        return [
            'anime' => ['poster-frieren.png', 'attack-on-titan-poster.png', 'gojo-portrait.png', 'poster-anime-night.png', 'poster-anime-city.png', 'demon-slayer-banner.png'],
            'gaming' => ['elden-ring-poster.png', 'poster-hades.png', 'poster-game-roguelike.png', 'poster-game-party.png', 'poster-game-peak.png'],
            'movies' => ['miles-portrait.png', 'neon-chronicle-poster.png', 'poster-movie-desert.png', 'poster-movie-mind.png', 'poster-movie-multiverse.png', 'poster-movie-noir.png', 'poster-movie-orbit.png'],
            'tv-shows' => ['poster-tv.png', 'poster-tv-office.png', 'poster-tv-kitchen.png'],
            'k-pop' => ['poster-kpop.png', 'poster-kpop-stage.png'],
            'comics' => ['poster-comic.png', 'poster-comic-owl.png', 'merch-generic.png'],
            'manga' => ['one-piece-poster.png', 'poster-manga-alley.png', 'luffy-portrait.png'],
            'cosplay' => ['poster-cosplay.png', 'poster-craft.png', 'poster-cosplay-armor.png'],
        ];
    }

    private function contents(): void
    {
        $rows = [
            'Demon Slayer: Kimetsu no Yaiba' => ['demon-slayer-banner.png', 'demon-slayer-banner.png'],
            'One Piece' => ['one-piece-poster.png', 'one-piece-banner.png'],
            'Elden Ring' => ['elden-ring-poster.png', 'elden-ring-poster.png'],
            'Attack on Titan: The Final Season' => ['attack-on-titan-poster.png', 'attack-on-titan-poster.png'],
            'Spider-Man: Across the Spider-Verse' => ['miles-portrait.png', 'spiderverse-banner.png'],
            'Neon Chronicle Rising' => ['neon-chronicle-poster.png', 'neon-chronicle-poster.png'],
        ];

        foreach ($rows as $title => [$cover, $banner]) {
            Content::query()->where('title', $title)->update([
                'cover_image' => $this->path($cover),
                'banner_image' => $this->path($banner),
            ]);
        }
    }

    private function characters(): void
    {
        $rows = [
            'Monkey D. Luffy' => 'luffy-portrait.png',
            'Satoru Gojo' => 'gojo-portrait.png',
            'Tanjiro Kamado' => 'tanjiro-portrait.png',
            'Malenia, Blade of Miquella' => 'malenia-portrait.png',
            'Miles Morales' => 'miles-portrait.png',
        ];

        foreach ($rows as $name => $file) {
            Character::query()->where('name', $name)->update([
                'image' => $this->path($file),
            ]);
        }
    }

    private function articles(): void
    {
        $rows = [
            'Why Seasonal Charts Matter More Than Ever' => 'article-seasonal.png',
            'The Quiet Work of the Found-Family Trope' => 'article-found-family.png',
            'Speedrunning as Fandom Culture' => 'article-speedrun.png',
            'Blooming Nexus Protocol' => 'article-nexus.png',
        ];

        foreach ($rows as $title => $file) {
            Article::query()->where('title', $title)->update([
                'cover_image' => $this->path($file),
            ]);
        }
    }

    private function merchandise(): void
    {
        $rows = [
            'One Piece — Straw Hat Wearable Replica' => 'merch-straw-hat.png',
            'Elden Ring — Cloth Map of the Lands Between' => 'merch-elden-map.png',
            'NewJeans — Official Lightstick (2026 Ver.)' => 'merch-lightstick.png',
        ];

        foreach ($rows as $title => $file) {
            Merchandise::query()->where('title', $title)->update([
                'image' => $this->path($file),
            ]);
        }
    }

    private function releases(): void
    {
        $byContent = [
            'one-piece' => 'one-piece-poster.png',
            'demon-slayer-kimetsu-no-yaiba' => 'demon-slayer-banner.png',
            'elden-ring' => 'elden-ring-poster.png',
            'attack-on-titan-the-final-season' => 'attack-on-titan-poster.png',
            'spider-man-across-the-spider-verse' => 'spiderverse-banner.png',
        ];

        foreach ($byContent as $slug => $file) {
            $contentId = Content::query()->where('slug', $slug)->value('id');

            if ($contentId === null) {
                continue;
            }

            Release::query()->where('content_id', $contentId)->update([
                'poster' => $this->path($file),
            ]);
        }
    }

    private function categories(): void
    {
        foreach ($this->banners() as $slug => $file) {
            Category::query()->where('slug', $slug)->update([
                'image' => $this->path($file),
            ]);
        }
    }

    /**
     * Every catalogue row without a real still gets one from its fandom pool.
     */
    private function fillGaps(): void
    {
        $this->fill(Content::class, 'cover_image', 'banner_image');
        $this->fill(Character::class, 'image');
        $this->fill(Article::class, 'cover_image');
        $this->fill(Event::class, 'cover_image');
        $this->fill(Media::class, 'thumbnail');
        $this->fill(Merchandise::class, 'image');
        $this->fill(Release::class, 'poster');
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function fill(string $model, string $imageColumn, ?string $bannerColumn = null): void
    {
        $model::query()->with('category')->orderBy('id')->each(function (Model $row) use ($imageColumn, $bannerColumn): void {
            $slug = (string) ($row->getRelation('category')?->slug ?? 'anime');
            $updates = [];

            if (! $this->hasArt($row->getAttribute($imageColumn))) {
                $updates[$imageColumn] = $this->path($this->pick($slug, (int) $row->getKey()));
            }

            if ($bannerColumn !== null && ! $this->hasArt($row->getAttribute($bannerColumn))) {
                $updates[$bannerColumn] = $this->path($this->banners()[$slug] ?? 'banner-explore.png');
            }

            if ($updates !== []) {
                $row->update($updates);
            }
        });
    }

    private function pick(string $slug, int $id): string
    {
        $pool = $this->pools()[$slug] ?? $this->pools()['anime'];

        return $pool[$id % count($pool)];
    }

    /**
     * Give neighbouring cards different stills. Named highlights keep their art.
     */
    private function spreadDuplicates(): void
    {
        $this->spreadContent();
        $this->spread(Character::class, 'image', 'name', array_keys($this->characterFiles()), $this->portraitPool());
        $this->spread(Article::class, 'cover_image', 'title', array_keys($this->articleFiles()), $this->articlePool());
        $this->spread(Event::class, 'cover_image', 'title', [], $this->eventPool());
        $this->spread(Media::class, 'thumbnail', 'title', [], $this->posterPool());
        $this->spread(Merchandise::class, 'image', 'title', array_keys($this->merchFiles()), $this->merchPool());
        $this->spreadReleases();
    }

    private function spreadContent(): void
    {
        $locked = array_keys($this->contentFiles());
        $usage = [];
        $rows = Content::query()->with('category')->orderBy('id')->get();

        foreach ($rows as $row) {
            if (in_array($row->title, $locked, true)) {
                $this->markUsage($usage, (string) $row->cover_image);
            }
        }

        foreach ($rows as $row) {
            if (in_array($row->title, $locked, true)) {
                continue;
            }

            $slug = (string) ($row->category?->slug ?? 'anime');
            $file = $this->leastUsed($this->pools()[$slug] ?? $this->pools()['anime'], $usage);
            $this->markUsage($usage, $file);

            $row->update([
                'cover_image' => $this->path($file),
                'banner_image' => $this->path($file),
            ]);
        }
    }

    private function spreadReleases(): void
    {
        $lockedSlugs = [
            'one-piece',
            'demon-slayer-kimetsu-no-yaiba',
            'elden-ring',
            'attack-on-titan-the-final-season',
            'spider-man-across-the-spider-verse',
        ];
        $lockedIds = Content::query()->whereIn('slug', $lockedSlugs)->pluck('id')->all();
        $usage = [];
        $rows = Release::query()->orderBy('id')->get();

        foreach ($rows as $row) {
            if (in_array($row->content_id, $lockedIds, true)) {
                $this->markUsage($usage, (string) $row->poster);
            }
        }

        foreach ($rows as $row) {
            if (in_array($row->content_id, $lockedIds, true)) {
                continue;
            }

            $file = $this->leastUsed($this->posterPool(), $usage);
            $this->markUsage($usage, $file);
            $row->update(['poster' => $this->path($file)]);
        }
    }

    /**
     * @param  class-string<Model>  $model
     * @param  list<string>  $lockedNames
     * @param  list<string>  $preferred
     */
    private function spread(string $model, string $column, string $nameColumn, array $lockedNames, array $preferred): void
    {
        $usage = [];
        $rows = $model::query()->orderBy('id')->get();

        foreach ($rows as $row) {
            if (in_array((string) $row->getAttribute($nameColumn), $lockedNames, true)) {
                $this->markUsage($usage, (string) $row->getAttribute($column));
            }
        }

        foreach ($rows as $row) {
            if (in_array((string) $row->getAttribute($nameColumn), $lockedNames, true)) {
                continue;
            }

            $file = $this->leastUsed($preferred, $usage);
            $this->markUsage($usage, $file);
            $row->update([$column => $this->path($file)]);
        }
    }

    /**
     * @param  list<string>  $preferred
     * @param  array<string, int>  $usage
     */
    private function leastUsed(array $preferred, array $usage): string
    {
        foreach ($preferred as $file) {
            if (($usage[$file] ?? 0) === 0) {
                return $file;
            }
        }

        foreach ($this->catalogueFiles() as $file) {
            if (($usage[$file] ?? 0) === 0 && ! str_starts_with($file, 'banner-')) {
                return $file;
            }
        }

        $best = $preferred[0];
        $bestCount = PHP_INT_MAX;

        foreach ($preferred as $file) {
            $count = $usage[$file] ?? 0;

            if ($count < $bestCount) {
                $best = $file;
                $bestCount = $count;
            }
        }

        return $best;
    }

    /**
     * @param  array<string, int>  $usage
     */
    private function markUsage(array &$usage, string $value): void
    {
        $file = str_contains($value, '/') ? basename($value) : $value;

        if ($file === '') {
            return;
        }

        $usage[$file] = ($usage[$file] ?? 0) + 1;
    }

    /**
     * @return array<string, string>
     */
    private function contentFiles(): array
    {
        return [
            'Demon Slayer: Kimetsu no Yaiba' => 'demon-slayer-banner.png',
            'One Piece' => 'one-piece-poster.png',
            'Elden Ring' => 'elden-ring-poster.png',
            'Attack on Titan: The Final Season' => 'attack-on-titan-poster.png',
            'Spider-Man: Across the Spider-Verse' => 'miles-portrait.png',
            'Neon Chronicle Rising' => 'neon-chronicle-poster.png',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function characterFiles(): array
    {
        return [
            'Monkey D. Luffy' => 'luffy-portrait.png',
            'Satoru Gojo' => 'gojo-portrait.png',
            'Tanjiro Kamado' => 'tanjiro-portrait.png',
            'Malenia, Blade of Miquella' => 'malenia-portrait.png',
            'Miles Morales' => 'miles-portrait.png',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function articleFiles(): array
    {
        return [
            'Why Seasonal Charts Matter More Than Ever' => 'article-seasonal.png',
            'The Quiet Work of the Found-Family Trope' => 'article-found-family.png',
            'Speedrunning as Fandom Culture' => 'article-speedrun.png',
            'Blooming Nexus Protocol' => 'article-nexus.png',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function merchFiles(): array
    {
        return [
            'One Piece — Straw Hat Wearable Replica' => 'merch-straw-hat.png',
            'Elden Ring — Cloth Map of the Lands Between' => 'merch-elden-map.png',
            'NewJeans — Official Lightstick (2026 Ver.)' => 'merch-lightstick.png',
        ];
    }

    /**
     * @return list<string>
     */
    private function portraitPool(): array
    {
        return [
            'luffy-portrait.png',
            'gojo-portrait.png',
            'tanjiro-portrait.png',
            'malenia-portrait.png',
            'miles-portrait.png',
            'face-scarf.png',
            'face-silver.png',
            'poster-anime-city.png',
            'poster-anime-night.png',
            'poster-cosplay-armor.png',
            'poster-kpop-stage.png',
            'poster-comic-owl.png',
            'poster-manga-alley.png',
            'poster-game-peak.png',
            'poster-tv-kitchen.png',
            'poster-frieren.png',
            'poster-hades.png',
        ];
    }

    /**
     * @return list<string>
     */
    private function articlePool(): array
    {
        return [
            'article-seasonal.png',
            'article-found-family.png',
            'article-speedrun.png',
            'article-nexus.png',
            'poster-tv-office.png',
            'poster-tv-kitchen.png',
            'poster-movie-mind.png',
            'poster-anime-city.png',
        ];
    }

    /**
     * @return list<string>
     */
    private function eventPool(): array
    {
        return [
            'poster-kpop-stage.png',
            'poster-game-party.png',
            'poster-cosplay-armor.png',
            'poster-comic-owl.png',
            'poster-anime-night.png',
            'poster-movie-multiverse.png',
            'poster-tv-kitchen.png',
            'poster-game-roguelike.png',
            'poster-manga-alley.png',
        ];
    }

    /**
     * @return list<string>
     */
    private function merchPool(): array
    {
        return [
            'merch-straw-hat.png',
            'merch-elden-map.png',
            'merch-lightstick.png',
            'merch-generic.png',
            'poster-kpop-stage.png',
            'poster-cosplay-armor.png',
            'poster-comic.png',
            'poster-game-party.png',
            'poster-movie-noir.png',
            'poster-anime-city.png',
        ];
    }

    /**
     * @return list<string>
     */
    private function posterPool(): array
    {
        return array_values(array_filter(
            $this->catalogueFiles(),
            fn (string $file): bool => ! str_starts_with($file, 'banner-')
        ));
    }

    /**
     * @return list<string>
     */
    private function catalogueFiles(): array
    {
        return collect(File::files(public_path('images/catalogue')))
            ->map(fn ($file): string => $file->getFilename())
            ->filter(fn (string $file): bool => str_ends_with(strtolower($file), '.png'))
            ->sort()
            ->values()
            ->all();
    }

    private function hasArt(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, '/images/');
    }

    private function path(string $file): string
    {
        return '/images/catalogue/'.$file;
    }
}
