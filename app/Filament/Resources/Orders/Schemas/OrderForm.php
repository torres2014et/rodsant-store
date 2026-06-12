<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Estado del pedido')
                    ->description('Actualiza aquí en qué punto va el pedido a medida que avanza.')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Estado del pedido')
                            ->options(OrderStatus::options())
                            ->required()
                            ->native(false),

                        Select::make('payment_status')
                            ->label('Estado del pago')
                            ->options(PaymentStatus::options())
                            ->required()
                            ->native(false),

                        Placeholder::make('order_number')
                            ->label('N.º de pedido')
                            ->content(fn (?Order $record): string => $record?->order_number ?? '—'),

                        Placeholder::make('created_at')
                            ->label('Fecha del pedido')
                            ->content(fn (?Order $record): string => $record?->created_at?->format('d/m/Y H:i') ?? '—'),
                    ]),

                Section::make('Datos del cliente')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('customer_name')
                            ->label('Nombre')
                            ->content(fn (?Order $record): string => $record?->customer_name ?? '—'),

                        Placeholder::make('customer_phone')
                            ->label('WhatsApp / teléfono')
                            ->content(fn (?Order $record): string => $record?->customer_phone ?? '—'),

                        Placeholder::make('customer_email')
                            ->label('Correo')
                            ->content(fn (?Order $record): string => $record?->customer_email ?? '—'),

                        Placeholder::make('payment_method')
                            ->label('Método de pago')
                            ->content(fn (?Order $record): string => $record?->payment_method?->label() ?? '—'),
                    ]),

                Section::make('Productos del pedido')
                    ->schema([
                        Placeholder::make('items')
                            ->label('')
                            ->content(fn (?Order $record): HtmlString => static::itemsTable($record)),
                    ]),

                Section::make('Notas internas')
                    ->description('Solo para uso del equipo. El cliente no las ve.')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->maxLength(1000),
                    ]),
            ]);
    }

    /**
     * Renderiza los artículos del pedido (snapshot inmutable) y los totales como tabla legible.
     */
    protected static function itemsTable(?Order $record): HtmlString
    {
        if ($record === null || $record->items->isEmpty()) {
            return new HtmlString('<p class="text-sm text-gray-500">Sin artículos.</p>');
        }

        $symbol = (string) config('rodsant.currency.symbol');
        $decimals = (int) config('rodsant.currency.decimals');
        $money = static fn (float $value): string => $symbol.number_format($value, $decimals, ',', '.');

        $rows = '';
        foreach ($record->items as $item) {
            $name = e($item->product_name);
            $variant = $item->variant_label ? '<span class="text-gray-500"> · '.e($item->variant_label).'</span>' : '';
            $rows .= '<tr class="border-b border-gray-100 dark:border-white/10">'
                .'<td class="py-2 pr-4">'.$name.$variant.'</td>'
                .'<td class="py-2 px-4 text-center">'.(int) $item->quantity.'</td>'
                .'<td class="py-2 px-4 text-right">'.$money((float) $item->unit_price).'</td>'
                .'<td class="py-2 pl-4 text-right font-semibold">'.$money((float) $item->line_total).'</td>'
                .'</tr>';
        }

        $totalRow = static fn (string $label, float $value, bool $strong = false): string => $value != 0.0 || $strong
            ? '<tr>'
                .'<td colspan="3" class="py-1 pr-4 text-right '.($strong ? 'font-bold text-base' : 'text-gray-500').'">'.$label.'</td>'
                .'<td class="py-1 pl-4 text-right '.($strong ? 'font-bold text-base' : '').'">'.$money($value).'</td>'
                .'</tr>'
            : '';

        $totals = $totalRow('Subtotal', (float) $record->subtotal)
            .$totalRow('Descuento', -1 * (float) $record->discount_total)
            .$totalRow('Envío', (float) $record->shipping_total)
            .$totalRow('Total', (float) $record->grand_total, true);

        $html = '<div class="overflow-x-auto"><table class="w-full text-sm">'
            .'<thead><tr class="border-b border-gray-200 dark:border-white/20 text-left text-xs uppercase tracking-wide text-gray-500">'
            .'<th class="py-2 pr-4">Producto</th>'
            .'<th class="py-2 px-4 text-center">Cant.</th>'
            .'<th class="py-2 px-4 text-right">Precio</th>'
            .'<th class="py-2 pl-4 text-right">Total</th>'
            .'</tr></thead>'
            .'<tbody>'.$rows.'</tbody>'
            .'<tfoot>'.$totals.'</tfoot>'
            .'</table></div>';

        return new HtmlString($html);
    }
}
