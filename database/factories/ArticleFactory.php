<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
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
            'author_id' => User::factory(),
            'excerpt' => $this->blurb(),
            'body' => $this->longCopy(fake()->numberBetween(3, 5)),
            'cover_image' => null,
            'status' => Article::STATUS_PUBLISHED,
            'reading_time' => fake()->numberBetween(3, 14),
            'views_count' => fake()->numberBetween(40, 60000),
            'featured' => fake()->boolean(25),
            'published_at' => now()->subDays(fake()->numberBetween(0, 180)),
        ];
    }

    /**
     * Indicate that the article is still a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Article::STATUS_DRAFT,
            'published_at' => null,
            'featured' => false,
        ]);
    }

    /**
     * Indicate that the article is promoted on the home page.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'featured' => true,
        ]);
    }
}
