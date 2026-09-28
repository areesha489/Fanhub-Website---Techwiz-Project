<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Merchandise;
use Database\Factories\Concerns\GeneratesFandomCopy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Merchandise>
 */
class MerchandiseFactory extends Factory
{
    use GeneratesFandomCopy;

    /**
     * Product silhouettes available in the demo shop.
     *
     * @var list<string>
     */
    protected const PRODUCT_TYPES = [
        'Figure', 'Pin Set', 'Artbook', 'Hoodie', 'Poster', 'Plush', 'Model Kit', 'Tote Bag',
    ];

    /**
     * Product labels used for the JSON tag column.
     *
     * @var list<string>
     */
    protected const LABELS = [
        'Collectible', 'Limited edition', 'Preorder', 'Exclusive', 'Retro', 'Handmade', 'Deluxe', 'Bundle',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->uniqueTitle().' '.fake()->randomElement(self::PRODUCT_TYPES);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category_id' => Category::factory(),
            'fandom' => $this->fandom(),
            'description' => $this->blurb(),
            'image' => null,
            'price' => fake()->randomFloat(2, 14.99, 349.99),
            'currency' => 'USD',
            'tags' => fake()->randomElements(self::LABELS, 2),
            'is_upcoming' => fake()->boolean(15),
            'release_date' => fake()->date('Y-m-d', '+1 year'),
            'popularity' => fake()->numberBetween(5, 100),
            'status' => Merchandise::STATUS_PUBLISHED,
        ];
    }

    /**
     * Indicate that the product has not shipped yet.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_upcoming' => true,
            'release_date' => fake()->date('Y-m-d', '+18 months'),
        ]);
    }
}
