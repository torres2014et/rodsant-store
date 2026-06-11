<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanAbandonedCarts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'rodsant:clean-abandoned-carts {--hours= : Horas de inactividad antes de eliminar}';

    /**
     * @var string
     */
    protected $description = 'Elimina los carritos abandonados (sin actividad) según el umbral configurado.';

    public function handle(): int
    {
        $hours = (int) ($this->option('hours') ?? config('rodsant.carts.abandoned_after_hours'));
        $threshold = Carbon::now()->subHours($hours);

        $deleted = Cart::query()
            ->where('updated_at', '<', $threshold)
            ->whereDoesntHave('items', function ($query): void {
                $query->where('updated_at', '>=', now()->subHours(1));
            })
            ->delete();

        $this->info("Carritos abandonados eliminados: {$deleted} (inactivos > {$hours}h).");

        return self::SUCCESS;
    }
}
