<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory;

use App\Filament\Resources\Inventory\Pages\EditInventory;
use App\Filament\Resources\Inventory\Pages\ListInventory;
use App\Filament\Resources\Inventory\Schemas\InventoryForm;
use App\Filament\Resources\Inventory\Tables\InventoryTable;
use App\Models\Inventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'inventory';

    public static function getModelLabel(): string
    {
        return 'inventario';
    }

    public static function getPluralModelLabel(): string
    {
        return 'inventario';
    }

    public static function getNavigationLabel(): string
    {
        return 'Inventario';
    }

    public static function getNavigationBadge(): ?string
    {
        $needsAttention = static::getModel()::query()
            ->whereRaw('quantity - reserved <= low_stock_threshold')
            ->count();

        return $needsAttention > 0 ? (string) $needsAttention : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Variantes agotadas o con stock bajo';
    }

    public static function form(Schema $schema): Schema
    {
        return InventoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventory::route('/'),
            'edit' => EditInventory::route('/{record}/edit'),
        ];
    }
}
