<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Tables;

use App\Models\Inventory;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'variant.product',
                'variant.attributeValues.attribute',
            ]))
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Inventory $record): ?string => $record->variant?->label() ?: null),

                TextColumn::make('variant.sku')
                    ->label('SKU')
                    ->searchable()
                    ->color('gray')
                    ->toggleable(),

                TextInputColumn::make('quantity')
                    ->label('En stock')
                    ->type('number')
                    ->rules(['required', 'integer', 'min:0'])
                    ->width('7rem'),

                TextColumn::make('reserved')
                    ->label('Reservado')
                    ->numeric()
                    ->color('gray')
                    ->tooltip('Unidades apartadas en carritos activos. Lo gestiona la tienda automáticamente.'),

                TextColumn::make('available')
                    ->label('Disponible')
                    ->state(fn (Inventory $record): int => $record->available())
                    ->badge()
                    ->color(fn (Inventory $record): string => $record->variant?->stockStatus()->color() ?? 'gray'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->state(fn (Inventory $record): string => $record->variant?->stockStatus()->label() ?? '—')
                    ->badge()
                    ->color(fn (Inventory $record): string => $record->variant?->stockStatus()->color() ?? 'gray'),

                TextInputColumn::make('low_stock_threshold')
                    ->label('Aviso bajo stock')
                    ->type('number')
                    ->rules(['required', 'integer', 'min:0'])
                    ->width('8rem')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label('Producto')
                    ->relationship('variant.product', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('out_of_stock')
                    ->label('Solo agotados')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quantity - reserved <= 0')),

                Filter::make('low_stock')
                    ->label('Solo stock bajo')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereRaw('quantity - reserved > 0')
                        ->whereRaw('quantity - reserved <= low_stock_threshold')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('quantity', 'asc');
    }
}
