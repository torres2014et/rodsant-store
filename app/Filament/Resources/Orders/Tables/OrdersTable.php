<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('N.º de pedido')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Número copiado'),

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn ($record): ?string => $record->customer_phone),

                SelectColumn::make('status')
                    ->label('Estado')
                    ->options(OrderStatus::options())
                    ->selectablePlaceholder(false)
                    ->rules(['required'])
                    ->width('11rem'),

                TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                    ->color(fn (PaymentStatus $state): string => $state->color()),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('COP', locale: 'es_CO')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('items_count')
                    ->label('Artículos')
                    ->counts('items')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('whatsapp_sent_at')
                    ->label('WhatsApp')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No enviado')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(OrderStatus::options()),
                SelectFilter::make('payment_status')
                    ->label('Estado del pago')
                    ->options(PaymentStatus::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
