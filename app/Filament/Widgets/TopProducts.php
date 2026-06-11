<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProducts extends TableWidget
{
    protected static ?string $heading = 'Productos más vendidos';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Product::query()
                ->select('products.*')
                ->selectRaw('(
                    SELECT COALESCE(SUM(order_items.quantity), 0)
                    FROM order_items
                    INNER JOIN product_variants ON product_variants.id = order_items.product_variant_id
                    WHERE product_variants.product_id = products.id
                ) AS sold_count')
                ->orderByDesc('sold_count'))
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge(),
                TextColumn::make('sold_count')
                    ->label('Unidades vendidas')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_formatted')
                    ->label('Precio'),
            ]);
    }
}
