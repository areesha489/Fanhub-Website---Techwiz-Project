<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Release;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Release>
 */
class ReleaseFactory extends Factory
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
            'release_type' => fake()->randomElement(Release::TYPES),
            'category_id' => Category::factory(),
            'release_date' => fake()->date('Y-m-d', '+14 months'),
            'description' => $this->blurb(),
            'poster' => null,
            'status' => fake()->randomElement([
                Release::STATUS_UPCOMING,
                Release::STATUS_UPCOMING,
                Release::STATUS_RELEASED,
                Release::STATUS_DELAYED,
            ]),
            'content_id' => null,
            'popularity' => fake()->numberBetween(5, 100),
        ];
    }

    /**
     * Indicate that the launch already happened.
     */
    public function released(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Release::STATUS_RELEASED,
            'release_date' => fake()->date('Y-m-d', '-6 months'),
        ]);
    }
}
