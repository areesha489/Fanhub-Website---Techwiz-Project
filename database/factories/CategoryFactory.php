<?php

namespace Database\Factories;

use App\Models\Category;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    use GeneratesFandomCopy;

    /**
     * Category labels used by the demo catalogue.
     *
     * @var list<string>
     */
    protected const BASE_NAMES = [
        'Anime', 'Gaming', 'Movies', 'TV Shows', 'K-Pop', 'Comics', 'Manga', 'Cosplay',
        'Esports', 'Streaming', 'Horror', 'Sci-Fi', 'Fantasy', 'Music', 'Collectibles', 'Webtoons',
        'Podcasts', 'Conventions', 'Fan Art', 'Reviews', 'Retro', 'Mobile Games', 'Indie Games', 'Animation',
        'Light Novels', 'Card Games', 'Board Games', 'Wrestling', 'Football', 'Basketball',
        'Reality TV', 'Documentaries', 'Cyberpunk', 'Superheroes', 'Vampires', 'Mecha',
        'Isekai', 'Tournaments', 'Soundtracks', 'Cosplay Builds',
    ];

    /**
     * Qualifiers appended to keep category names unique at scale.
     *
     * @var list<string>
     */
    protected const QUALIFIERS = [
        '', 'Central', 'Spotlight', 'Weekly', 'Classics', 'Live', 'Archive', 'Plus',
    ];

    /**
     * Navigation icons from bootstrap-icons.
     *
     * @var list<string>
     */
    protected const ICONS = [
        'bi-fire', 'bi-controller', 'bi-film', 'bi-tv', 'bi-music-note-beamed', 'bi-book', 'bi-bookshelf',
        'bi-masks-theater', 'bi-trophy', 'bi-globe2', 'bi-robot', 'bi-people', 'bi-lightning', 'bi-star',
        'bi-palette', 'bi-camera-video', 'bi-puzzle', 'bi-rocket-takeoff',
    ];

    /**
     * Accent colours from the Fan Hub+ palette.
     *
     * @var list<string>
     */
    protected const ACCENTS = ['#8B5CF6', '#EC4899', '#22D3EE', '#3B82F6', '#34D399', '#FBBF24', '#FB7185'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseCount = count(self::BASE_NAMES);
        $qualifierCount = count(self::QUALIFIERS);
        $index = fake()->unique()->numberBetween(0, ($baseCount * $qualifierCount) - 1);

        $name = self::BASE_NAMES[$index % $baseCount];
        $qualifier = self::QUALIFIERS[intdiv($index, $baseCount) % $qualifierCount];

        if ($qualifier !== '') {
            $name .= ' '.$qualifier;
        }

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => fake()->randomElement(self::ICONS),
            'description' => $this->blurb(),
            'image' => null,
            'accent' => fake()->randomElement(self::ACCENTS),
            'position' => fake()->numberBetween(0, 30),
        ];
    }
}
