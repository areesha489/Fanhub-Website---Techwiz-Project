<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\FanSubmission;
use App\Models\User;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FanSubmission>
 */
class FanSubmissionFactory extends Factory
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
            'user_id' => User::factory(),
            'title' => $title,
            'type' => fake()->randomElement(FanSubmission::TYPES),
            'category_id' => fake()->boolean(70) ? Category::factory() : null,
            'description' => $this->blurb(),
            'url' => fake()->boolean(50) ? 'https://fanhubplus.example/submissions/'.Str::slug($title) : null,
            'image' => null,
            'status' => FanSubmission::STATUS_PENDING,
            'admin_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * Indicate that a moderator approved the submission.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FanSubmission::STATUS_APPROVED,
            'reviewed_by' => User::factory()->admin(),
            'reviewed_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Indicate that a moderator rejected the submission.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FanSubmission::STATUS_REJECTED,
            'reviewed_by' => User::factory()->admin(),
            'reviewed_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'admin_note' => $this->blurb(),
        ]);
    }
}
