<?php

namespace Tests\Feature;

use Database\Seeders\AttributeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * La portada pública responde correctamente.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed([
            AttributeSeeder::class,
            CategorySeeder::class,
            SettingSeeder::class,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
