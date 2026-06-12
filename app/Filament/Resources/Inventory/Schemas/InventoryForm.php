<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Schemas;

use App\Models\Inventory;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Variante')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('producto')
                            ->label('Producto')
                            ->content(fn (?Inventory $record): string => $record?->variant?->product?->name ?? '—'),

                        Placeholder::make('variante')
                            ->label('Talla / color')
                            ->content(fn (?Inventory $record): string => ($record?->variant?->label() ?: '—')),

                        Placeholder::make('sku')
                            ->label('SKU')
                            ->content(fn (?Inventory $record): string => $record?->variant?->sku ?? '—'),

                        Placeholder::make('reserved')
                            ->label('Reservado en carritos')
                            ->content(fn (?Inventory $record): string => (string) ($record?->reserved ?? 0)),
                    ]),

                Section::make('Existencias')
                    ->description('Disponible para vender = en stock − reservado.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Unidades en stock')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('Cantidad física que tienes de esta variante.'),

                        TextInput::make('low_stock_threshold')
                            ->label('Avisar cuando queden')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->suffix('unidades')
                            ->helperText('Al llegar a este número se marca como "últimas unidades".'),

                        Placeholder::make('available')
                            ->label('Disponible para vender ahora')
                            ->content(fn (?Inventory $record): string => (string) ($record?->available() ?? 0)),
                    ]),
            ]);
    }
}
