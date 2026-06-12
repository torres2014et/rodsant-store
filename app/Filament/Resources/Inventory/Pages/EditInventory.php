<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Pages;

use App\Filament\Resources\Inventory\InventoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Inventario actualizado')
            ->body('El stock se guardó correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
