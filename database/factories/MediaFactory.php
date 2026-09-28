<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Media;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    use GeneratesFandomCopy;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->uniqueTitle();

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category_id' => Category::factory(),
            'media_type' => fake()->randomElement(Media::TYPES),
            'description' => $this->blurb(),
            'thumbnail' => null,
            'url' => fake()->boolean(40) ? 'https://cdn.fanhubplus.example/'.Str::slug($title).'.webp' : null,
            'embed_url' => fake()->boolean(70)
                ? 'https://www.youtube.com/watch?v='.fake()->lexify('???????????')
                : null,
            'duration' => sprintf('%d:%02d', fake()->numberBetween(1, 24), fake()->numberBetween(0, 59)),
            'gallery' => null,
            'views_count' => fake()->numberBetween(80, 120000),
            'rating' => fake()->randomFloat(1, 3, 5),
            'featured' => fake()->boolean(25),
            'status' => Media::STATUS_PUBLISHED,
            'created_by' => null,
        ];
    }

    /**
     * Indicate that the entry is promoted on the home page.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'featured' => true,
        ]);
    }
}
