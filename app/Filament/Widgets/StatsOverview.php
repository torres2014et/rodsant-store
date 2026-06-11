<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $symbol = (string) config('rodsant.currency.symbol');

        // Ventas (pedidos no cancelados).
        $salesToday = (float) Order::query()
            ->whereDate('created_at', today())
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->sum('grand_total');

        $salesMonth = (float) Order::query()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->sum('grand_total');

        // Pedidos por estado.
        $pending = Order::query()->where('status', OrderStatus::Pending->value)->count();
        $delivered = Order::query()->where('status', OrderStatus::Delivered->value)->count();

        // Productos agotados (sin stock disponible en ninguna variante).
        $outOfStock = Product::query()
            ->where('is_active', true)
            ->whereDoesntHave('variants.inventory', function (Builder $q): void {
                $q->whereRaw('quantity - reserved > 0');
            })
            ->count();

        // Productos con stock bajo (alguna variante con stock > 0 pero bajo el umbral).
        $lowStock = Product::query()
            ->where('is_active', true)
            ->whereHas('variants.inventory', function (Builder $q): void {
                $q->whereRaw('quantity - reserved > 0')
                    ->whereRaw('quantity - reserved <= low_stock_threshold');
            })
            ->count();

        return [
            Stat::make('Ventas de hoy', $symbol.number_format($salesToday, 0, ',', '.'))
                ->description('Pedidos de hoy (sin cancelados)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Ventas del mes', $symbol.number_format($salesMonth, 0, ',', '.'))
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Pedidos pendientes', (string) $pending)
                ->description('Requieren gestión')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'gray'),

            Stat::make('Pedidos entregados', (string) $delivered)
                ->description('Completados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Productos agotados', (string) $outOfStock)
                ->description('Sin stock disponible')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStock > 0 ? 'danger' : 'gray'),

            Stat::make('Stock bajo', (string) $lowStock)
                ->description('Últimas unidades')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'warning' : 'gray'),
        ];
    }
}
