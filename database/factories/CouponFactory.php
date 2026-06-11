<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'type' => $this->faker->randomElement(['percent', 'fixed']),
            'value' => $this->faker->randomElement([10, 15, 20, 30000, 50000]),
            'min_subtotal' => $this->faker->optional()->randomElement([100000, 200000]),
            'usage_limit' => $this->faker->optional()->numberBetween(10, 100),
            'used_count' => 0,
            'starts_at' => null,
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ];
    }
}
