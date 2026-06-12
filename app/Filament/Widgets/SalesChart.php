<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

/**
 * Gráfica de barras con la evolución de las ventas (pedidos no cancelados).
 * Incluye un selector para ver por semanas o por meses. Los periodos se
 * calculan en PHP para funcionar igual en MySQL (producción) y sqlite (tests).
 */
class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Evolución de ventas';

    protected ?string $pollingInterval = '60s';

    protected static ?int $sort = 2;

    public ?string $filter = 'month';

    /**
     * Opciones del selector (arriba a la derecha de la gráfica).
     *
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            'week' => 'Últimas 8 semanas',
            'month' => 'Últimos 6 meses',
        ];
    }

    /**
     * @return array{datasets: array<int, array<string, mixed>>, labels: array<int, string>}
     */
    protected function getData(): array
    {
        [$labels, $totals] = $this->filter === 'week'
            ? $this->salesByWeek()
            : $this->salesByMonth();

        return [
            'datasets' => [
                [
                    'label' => 'Ventas ('.config('rodsant.currency.symbol').')',
                    'data' => $totals,
                    'backgroundColor' => '#111111',
                    'borderColor' => '#111111',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Suma de ventas de las últimas 8 semanas.
     *
     * @return array{0: array<int, string>, 1: array<int, float>}
     */
    private function salesByWeek(): array
    {
        $labels = [];
        $totals = [];

        for ($i = 7; $i >= 0; $i--) {
            $start = CarbonImmutable::now()->startOfWeek()->subWeeks($i);
            $end = $start->endOfWeek();

            $labels[] = $start->format('d/m');
            $totals[] = $this->sumSales($start, $end);
        }

        return [$labels, $totals];
    }

    /**
     * Suma de ventas de los últimos 6 meses.
     *
     * @return array{0: array<int, string>, 1: array<int, float>}
     */
    private function salesByMonth(): array
    {
        $labels = [];
        $totals = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = CarbonImmutable::now()->startOfMonth()->subMonths($i);
            $end = $start->endOfMonth();

            $labels[] = ucfirst($start->translatedFormat('M Y'));
            $totals[] = $this->sumSales($start, $end);
        }

        return [$labels, $totals];
    }

    /**
     * Total facturado (sin cancelados) en un rango de fechas.
     */
    private function sumSales(CarbonImmutable $start, CarbonImmutable $end): float
    {
        return (float) Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->sum('grand_total');
    }
}
