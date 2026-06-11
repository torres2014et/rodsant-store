<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'customer_id' => Customer::factory(),
            'rating' => $this->faker->numberBetween(3, 5),
            'title' => $this->faker->optional()->sentence(3),
            'body' => $this->faker->paragraph(),
            'is_approved' => $this->faker->boolean(70),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => ['is_approved' => true]);
    }
}
