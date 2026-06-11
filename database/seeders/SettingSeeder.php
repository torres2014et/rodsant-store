<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'store' => [
                'name' => 'RodSant Store',
                'tagline' => 'Diseñado para moverse',
                'email' => 'contacto@rodsantstore.com',
                'address' => 'Colombia',
            ],
            'whatsapp_number' => config('rodsant.whatsapp.number'),
            'currency' => [
                'code' => config('rodsant.currency.code'),
                'symbol' => config('rodsant.currency.symbol'),
            ],
            'free_shipping_from' => config('rodsant.shipping.free_from'),
            'social_links' => [
                'instagram' => 'https://instagram.com/rodsantstore',
                'tiktok' => null,
                'facebook' => null,
            ],
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value, 'general');
        }
    }
}
