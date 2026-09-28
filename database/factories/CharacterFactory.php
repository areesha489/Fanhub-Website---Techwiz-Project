<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Character;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Character>
 */
class CharacterFactory extends Factory
{
    use GeneratesFandomCopy;

    /**
     * Signature abilities assigned to demo characters.
     *
     * @var list<string>
     */
    protected const ABILITIES = [
        'Portal crafting and short-range teleportation',
        'Swordsmanship built on borrowed memories',
        'Sound-based precognition in crowded cities',
        'Gravity wells and low-orbit freefalls',
        'Beast bonding and field medicine',
        'Hacking, drones and improvised gadgets',
        'Elemental blades with tactical forecasting',
        'Time-loop awareness and pattern analysis',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();
        $fandom = $this->fandom();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category_id' => Category::factory(),
            'fandom' => $fandom,
            'image' => null,
            'gallery' => null,
            'bio' => $this->blurb(),
            'gender' => fake()->randomElement(['Female', 'Male', 'Non-binary', 'Unknown']),
            'age' => (string) fake()->numberBetween(16, 70),
            'affiliation' => $fandom.' '.fake()->randomElement(['Guild', 'Academy', 'Fleet', 'Resistance', 'Syndicate', 'Collective']),
            'abilities' => fake()->randomElement(self::ABILITIES),
            'debut' => fake()->date('Y-m-d', '-15 years'),
            'views_count' => fake()->numberBetween(120, 90000),
            'popularity' => fake()->numberBetween(5, 100),
            'featured' => fake()->boolean(20),
        ];
    }

    /**
     * Indicate that the character is promoted on the home page.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'featured' => true,
            'popularity' => fake()->numberBetween(85, 100),
        ]);
    }
}
