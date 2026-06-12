<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la categoría')
                    ->description('Datos principales que organizan tu tienda.')
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

                        Select::make('parent_id')
                            ->label('Categoría superior (opcional)')
                            ->helperText('Déjala vacía si es una categoría principal.')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query, ?Category $record): Builder => $record
                                    ? $query->whereKeyNot($record->getKey())
                                    : $query,
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Categoría principal'),

                        Select::make('gender')
                            ->label('Dirigida a')
                            ->options([
                                'mujer' => 'Mujer',
                                'hombre' => 'Hombre',
                                'unisex' => 'Unisex',
                            ])
                            ->default('mujer')
                            ->required()
                            ->native(false),

                        Textarea::make('description')
                            ->label('Descripción (opcional)')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Imagen')
                    ->description('Foto que representa la categoría en la tienda.')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Imagen de la categoría')
                            ->image()
                            ->directory('categories')
                            ->imageEditor(),
                    ]),

                Section::make('Orden y visibilidad')
                    ->columns(2)
                    ->schema([
                        TextInput::make('position')
                            ->label('Orden de aparición')
                            ->helperText('Número más bajo aparece primero.')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Mostrar en la tienda')
                            ->default(true),
                    ]),
            ]);
    }
}
