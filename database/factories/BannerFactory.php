<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BannerType;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'type' => BannerType::Hero,
            'title' => $this->faker->words(3, true),
            'subtitle' => $this->faker->sentence(),
            'image_desktop' => null,
            'image_mobile' => null,
            'link_url' => '/shop',
            'cta_label' => 'Ver colección',
            'position' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    public function type(BannerType $type): static
    {
        return $this->state(fn (): array => ['type' => $type]);
    }
}
