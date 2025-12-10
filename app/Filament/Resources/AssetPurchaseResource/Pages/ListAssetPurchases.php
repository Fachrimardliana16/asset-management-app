<?php

namespace App\Filament\Resources\AssetPurchaseResource\Pages;

use App\Filament\Resources\AssetPurchaseResource;
use App\Filament\Resources\AssetPurchaseResource\Widgets\AssetPurchaseStatsWidget;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListAssetPurchases extends ListRecords
{
    protected static string $resource = AssetPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->default(now()->startOfMonth()),
                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->default(now()->endOfMonth()),
                ])
                ->action(function (array $data) {
                    $params = http_build_query([
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                    ]);

                    return redirect()->to(route('export.asset-purchase') . '?' . $params);
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetPurchaseStatsWidget::class,
        ];
    }
}
