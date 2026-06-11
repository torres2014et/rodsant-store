<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primaryImage.path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('images/placeholder.png')),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->short_description),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->sortable(),

                TextColumn::make('base_price')
                    ->label('Precio')
                    ->money('COP', locale: 'es_CO')
                    ->sortable(),

                TextColumn::make('sale_price')
                    ->label('Oferta')
                    ->money('COP', locale: 'es_CO')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('variants_count')
                    ->label('Variantes')
                    ->counts('variants')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('En tienda')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Visible en tienda'),
                TernaryFilter::make('is_featured')
                    ->label('Destacado'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
