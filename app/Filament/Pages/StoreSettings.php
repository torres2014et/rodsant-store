<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Configuración de la tienda gestionable por la propietaria (sin tocar código):
 * número de WhatsApp, datos de contacto y redes. Restringida a `settings.manage`.
 */
class StoreSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.store-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'Configuración';
    }

    public function getTitle(): string
    {
        return 'Configuración de la tienda';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.manage') ?? false;
    }

    public function mount(): void
    {
        $store = (array) Setting::get('store', []);
        $social = (array) Setting::get('social_links', []);

        $this->form->fill([
            'whatsapp_number' => (string) Setting::get('whatsapp_number', config('rodsant.whatsapp.number')),
            'store_name' => $store['name'] ?? config('rodsant.brand.name'),
            'store_email' => $store['email'] ?? null,
            'instagram' => $social['instagram'] ?? null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('WhatsApp')
                    ->description('A este número llegan los pedidos y los mensajes del botón flotante de la tienda.')
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('Número de WhatsApp')
                            ->helperText('Con indicativo de país y sin espacios ni signos. Ejemplo: 573001234567')
                            ->required()
                            ->tel()
                            ->prefix('+')
                            ->minLength(7)
                            ->maxLength(20),
                    ]),

                Section::make('Contacto y redes')
                    ->description('Aparecen en la tienda (pie de página, datos de contacto).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Nombre de la tienda')
                            ->maxLength(255),
                        TextInput::make('store_email')
                            ->label('Correo de contacto')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('instagram')
                            ->label('Instagram (enlace)')
                            ->url()
                            ->placeholder('https://instagram.com/rodsantstore')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $number = preg_replace('/\D+/', '', (string) ($data['whatsapp_number'] ?? '')) ?? '';

        $store = (array) Setting::get('store', []);
        $store['name'] = $data['store_name'] ?? ($store['name'] ?? null);
        $store['email'] = $data['store_email'] ?? ($store['email'] ?? null);

        $social = (array) Setting::get('social_links', []);
        $social['instagram'] = $data['instagram'] ?? null;

        Setting::set('whatsapp_number', $number);
        Setting::set('store', $store);
        Setting::set('social_links', $social);

        Notification::make()
            ->title('Configuración guardada')
            ->body('Los cambios ya están activos en la tienda.')
            ->success()
            ->send();
    }
}
