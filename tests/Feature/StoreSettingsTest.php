<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\StoreSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\Whatsapp;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StoreSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role): User
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }

    public function test_super_admin_can_open_settings_page(): void
    {
        $this->actingAs($this->user(UserRole::SuperAdmin))
            ->get('/admin/store-settings')
            ->assertOk()
            ->assertSee('Número de WhatsApp');
    }

    public function test_admin_without_permission_cannot_access(): void
    {
        $this->actingAs($this->user(UserRole::Admin))
            ->get('/admin/store-settings')
            ->assertForbidden();
    }

    public function test_saving_updates_whatsapp_and_contact(): void
    {
        $this->actingAs($this->user(UserRole::SuperAdmin));

        Livewire::test(StoreSettings::class)
            ->set('data.whatsapp_number', '57 300 111 2233')
            ->set('data.store_name', 'RodSant Store')
            ->set('data.store_email', 'hola@rodsant.com')
            ->set('data.instagram', 'https://instagram.com/rodsantstore')
            ->call('save')
            ->assertHasNoErrors();

        // Se guarda solo con dígitos.
        $this->assertSame('573001112233', Setting::get('whatsapp_number'));
        $this->assertSame('hola@rodsant.com', Setting::get('store')['email'] ?? null);
        $this->assertSame('https://instagram.com/rodsantstore', Setting::get('social_links')['instagram'] ?? null);
    }

    public function test_whatsapp_helper_reads_setting_and_builds_link(): void
    {
        Setting::set('whatsapp_number', '57-300-555-0000');

        $this->assertSame('573005550000', Whatsapp::number());
        $this->assertTrue(Whatsapp::isConfigured());
        $this->assertStringContainsString('wa.me/573005550000', Whatsapp::link('hola'));
        $this->assertStringContainsString('text=hola', Whatsapp::link('hola'));
    }
}
