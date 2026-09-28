<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Content;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
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
            'content_type' => fake()->randomElement(Content::TYPES),
            'genre' => $this->genre(),
            'description' => $this->blurb(),
            'synopsis' => $this->longCopy(2),
            'cover_image' => null,
            'banner_image' => null,
            'release_date' => fake()->date('Y-m-d', '-3 years'),
            'status' => Content::STATUS_PUBLISHED,
            'rating' => fake()->randomFloat(1, 3.4, 5),
            'views_count' => fake()->numberBetween(250, 250000),
            'popularity' => fake()->numberBetween(5, 100),
            'featured' => fake()->boolean(25),
            'trailer_url' => fake()->boolean(35)
                ? 'https://www.youtube.com/watch?v='.fake()->lexify('???????????')
                : null,
            'created_by' => null,
        ];
    }

    /**
     * Indicate that the title is still a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Content::STATUS_DRAFT,
            'featured' => false,
        ]);
    }

    /**
     * Indicate that the title is promoted on the home page.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'featured' => true,
            'popularity' => fake()->numberBetween(85, 100),
        ]);
    }
}
