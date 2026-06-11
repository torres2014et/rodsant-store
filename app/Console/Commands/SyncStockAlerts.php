<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Inventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStockAlerts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'rodsant:sync-stock-alerts';

    /**
     * @var string
     */
    protected $description = 'Reporta las variantes con bajo stock o agotadas para alertar a la administración.';

    public function handle(): int
    {
        $lowStock = Inventory::query()
            ->whereColumn('low_stock_threshold', '>=', DB::raw('quantity - reserved'))
            ->where(DB::raw('quantity - reserved'), '>', 0)
            ->count();

        $outOfStock = Inventory::query()
            ->where(DB::raw('quantity - reserved'), '<=', 0)
            ->count();

        $this->info("Variantes con bajo stock: {$lowStock}");
        $this->info("Variantes agotadas: {$outOfStock}");

        if ($lowStock > 0 || $outOfStock > 0) {
            $this->warn('Revisa el módulo de Inventario en el panel para reabastecer.');
        }

        return self::SUCCESS;
    }
}
