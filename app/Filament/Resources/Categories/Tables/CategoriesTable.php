<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Tables;

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

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Imagen')
                    ->circular()
                    ->defaultImageUrl(asset('images/placeholder.png')),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->parent?->name
                        ? 'Dentro de: '.$record->parent->name
                        : null),

                TextColumn::make('products_count')
                    ->label('Productos')
                    ->counts('products')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('gender')
                    ->label('Dirigida a')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('position')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('En tienda')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('parent')
                    ->label('Categoría superior')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Visible en tienda'),
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
            ->defaultSort('position', 'asc');
    }
}
