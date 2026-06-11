<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del producto')
                    ->description('Datos principales que verá el cliente en la tienda.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                                if ($operation === 'create' && $state !== null) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Enlace (slug)')
                            ->helperText('Se genera solo a partir del nombre. Puedes editarlo si lo necesitas.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('sku')
                            ->label('SKU (código interno)')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('short_description')
                            ->label('Descripción corta')
                            ->helperText('Frase breve que aparece bajo el nombre.')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('Descripción completa')
                            ->columnSpanFull(),
                    ]),

                Section::make('Precios')
                    ->columns(2)
                    ->schema([
                        TextInput::make('base_price')
                            ->label('Precio')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix(config('rodsant.currency.symbol')),

                        TextInput::make('sale_price')
                            ->label('Precio promocional (opcional)')
                            ->helperText('Déjalo vacío si no hay oferta.')
                            ->numeric()
                            ->minValue(0)
                            ->prefix(config('rodsant.currency.symbol')),
                    ]),

                Section::make('Imágenes del producto')
                    ->description('Arrastra para reordenar. Marca una como principal.')
                    ->schema([
                        Repeater::make('images')
                            ->label('')
                            ->relationship()
                            ->schema([
                                FileUpload::make('path')
                                    ->label('Imagen')
                                    ->image()
                                    ->directory('products')
                                    ->imageEditor()
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('alt')
                                    ->label('Texto alternativo (SEO/accesibilidad)')
                                    ->maxLength(255),
                                Toggle::make('is_primary')
                                    ->label('Imagen principal')
                                    ->inline(false),
                            ])
                            ->columns(2)
                            ->orderColumn('position')
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['alt'] ?? 'Imagen')
                            ->addActionLabel('Agregar imagen'),
                    ]),

                Section::make('Visibilidad')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Mostrar en la tienda')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('Destacado'),
                        Toggle::make('is_new')
                            ->label('Etiqueta "Nuevo"'),
                    ]),

                Section::make('SEO (posicionamiento en Google)')
                    ->description('Opcional. Si lo dejas vacío se usa el nombre del producto.')
                    ->collapsed()
                    ->columns(1)
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Título SEO')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('Descripción SEO')
                            ->maxLength(255)
                            ->rows(2),
                    ]),
            ]);
    }
}
