<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variantes (talla / color)';

    protected static ?string $recordTitleAttribute = 'sku';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('color_id')
                    ->label('Color')
                    ->options(fn (): array => self::valuesFor('color'))
                    ->required()
                    ->native(false),

                Select::make('talla_id')
                    ->label('Talla')
                    ->options(fn (): array => self::valuesFor('talla'))
                    ->required()
                    ->native(false),

                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->unique('product_variants', 'sku', ignoreRecord: true)
                    ->default(fn (): string => strtoupper(Str::random(10))),

                TextInput::make('stock')
                    ->label('Inventario (unidades)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Variante activa')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['attributeValues.attribute', 'inventory']))
            ->columns([
                TextColumn::make('label')
                    ->label('Variante')
                    ->state(fn (ProductVariant $record): string => $record->label() ?: '—')
                    ->weight('bold'),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('inventory.quantity')
                    ->label('Stock')
                    ->numeric()
                    ->badge()
                    ->color(fn (ProductVariant $record): string => $record->stockStatus()->color()),

                TextColumn::make('estado')
                    ->label('Disponibilidad')
                    ->state(fn (ProductVariant $record): string => $record->stockStatus()->label())
                    ->badge()
                    ->color(fn (ProductVariant $record): string => $record->stockStatus()->color()),

                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar variante')
                    ->using(fn (array $data): Model => $this->persistVariant($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->fillForm(fn (ProductVariant $record): array => $this->hydrateVariant($record))
                    ->using(fn (ProductVariant $record, array $data): Model => $this->persistVariant($data, $record)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    /**
     * Crea o actualiza una variante con sus atributos e inventario.
     *
     * @param  array<string, mixed>  $data
     */
    protected function persistVariant(array $data, ?ProductVariant $record = null): ProductVariant
    {
        $attributes = [
            'sku' => $data['sku'],
            'is_active' => $data['is_active'] ?? true,
        ];

        if ($record === null) {
            /** @var ProductVariant $record */
            $record = $this->getRelationship()->create($attributes);
        } else {
            $record->update($attributes);
        }

        $record->attributeValues()->sync(array_filter([
            $data['color_id'] ?? null,
            $data['talla_id'] ?? null,
        ]));

        $record->inventory()->updateOrCreate([], [
            'quantity' => (int) ($data['stock'] ?? 0),
            'low_stock_threshold' => (int) config('rodsant.inventory.low_stock_threshold'),
        ]);

        return $record;
    }

    /**
     * Carga los valores actuales de la variante en el formulario de edición.
     *
     * @return array<string, mixed>
     */
    protected function hydrateVariant(ProductVariant $record): array
    {
        $record->loadMissing(['attributeValues.attribute', 'inventory']);

        return [
            'sku' => $record->sku,
            'is_active' => $record->is_active,
            'color_id' => $record->attributeValues
                ->firstWhere(fn (AttributeValue $v): bool => $v->attribute->slug === 'color')?->id,
            'talla_id' => $record->attributeValues
                ->firstWhere(fn (AttributeValue $v): bool => $v->attribute->slug === 'talla')?->id,
            'stock' => $record->inventory?->quantity ?? 0,
        ];
    }

    /**
     * Opciones de un atributo (color/talla) como id => valor.
     *
     * @return array<int, string>
     */
    protected static function valuesFor(string $attributeSlug): array
    {
        return AttributeValue::query()
            ->whereHas('attribute', fn (Builder $q): Builder => $q->where('slug', $attributeSlug))
            ->orderBy('position')
            ->pluck('value', 'id')
            ->all();
    }
}
