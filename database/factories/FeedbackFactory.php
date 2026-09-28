<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    use GeneratesFandomCopy;

    /**
     * Subjects used by the demo feedback queue.
     *
     * @var list<string>
     */
    protected const SUBJECTS = [
        'Broken image on the character page',
        'Add a dark calendar view',
        'Filter events by timezone',
        'Praise for the new explore grid',
        'Chat widget covers the footer on mobile',
        'Export my bookmarks as JSON',
        'Subtitle track missing on a trailer',
        'Faster keyboard navigation in the admin table',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'type' => fake()->randomElement(Feedback::TYPES),
            'subject' => fake()->randomElement(self::SUBJECTS),
            'message' => $this->blurb(),
            'status' => Feedback::STATUS_OPEN,
            'admin_response' => null,
        ];
    }

    /**
     * Indicate that staff already replied.
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Feedback::STATUS_IN_REVIEW,
        ]);
    }
}
