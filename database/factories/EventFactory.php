<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use Database\Factories\Concerns\GeneratesFandomCopy;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    use GeneratesFandomCopy;

    /**
     * Demo venues for community gatherings.
     *
     * @var list<string>
     */
    protected const VENUES = [
        'Grand Expo Hall', 'Harbor Convention Center', 'Arena West', 'Civic Auditorium',
        'Skyline Pavilion', 'Riverside Studios', 'Union Exhibition Park', 'Northgate Theatre',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->uniqueTitle();
        $startDate = fake()->dateTimeBetween('+5 days', '+200 days')->format('Y-m-d');
        $endDate = (new DateTimeImmutable($startDate))
            ->modify('+'.fake()->numberBetween(0, 3).' days')
            ->format('Y-m-d');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category_id' => Category::factory(),
            'event_type' => fake()->randomElement(Event::TYPES),
            'description' => $this->blurb(),
            'cover_image' => null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'time' => sprintf('%02d:%02d', fake()->numberBetween(9, 19), fake()->randomElement([0, 15, 30, 45])),
            'venue' => fake()->randomElement(self::VENUES),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'ticket_url' => fake()->boolean(60) ? 'https://tickets.fanhubplus.example/'.Str::slug($name) : null,
            'status' => Event::STATUS_UPCOMING,
            'featured' => fake()->boolean(25),
            'views_count' => fake()->numberBetween(60, 40000),
        ];
    }

    /**
     * Indicate that the event already wrapped up.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Event::STATUS_COMPLETED,
            'start_date' => now()->subDays(fake()->numberBetween(10, 120))->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the event is promoted on the home page.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'featured' => true,
        ]);
    }
}
