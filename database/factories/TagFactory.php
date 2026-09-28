<?php

namespace Database\Factories;

use App\Models\Tag;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    use GeneratesFandomCopy;

    /**
     * Tag themes combined with subjects to keep tag names unique.
     *
     * @var list<string>
     */
    protected const THEMES = [
        'Action', 'Romance', 'Comedy', 'Retro', 'Competitive', 'Artistic',
        'Dark', 'Wholesome', 'Epic', 'Cozy', 'Chaotic', 'Classic',
    ];

    /**
     * @var list<string>
     */
    protected const SUBJECTS = [
        'Shonen', 'Isekai', 'Mecha', 'Cosplay', 'K-Pop', 'Speedrun',
        'Indie', 'Manga', 'Manhwa', 'Esports', 'Fanart', 'Trailers',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $themeCount = count(self::THEMES);
        $subjectCount = count(self::SUBJECTS);
        $index = fake()->unique()->numberBetween(0, ($themeCount * $subjectCount) - 1);

        $name = self::SUBJECTS[$index % $subjectCount].' '.self::THEMES[intdiv($index, $subjectCount) % $themeCount];

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
