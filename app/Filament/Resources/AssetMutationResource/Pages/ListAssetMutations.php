<?php

namespace App\Filament\Resources\AssetMutationResource\Pages;

use App\Filament\Resources\AssetMutationResource;
use App\Filament\Resources\AssetMutationResource\Widgets\AssetMutationStatsWidget;
use App\Models\MasterAssetsTransactionStatus;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListAssetMutations extends ListRecords
{
    protected static string $resource = AssetMutationResource::class;

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
                    Select::make('transaction_status')
                        ->label('Filter Jenis Mutasi')
                        ->options(MasterAssetsTransactionStatus::pluck('name', 'id'))
                        ->placeholder('Semua Jenis')
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'transaction_status' => $data['transaction_status'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset-mutation') . '?' . $params);
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetMutationStatsWidget::class,
        ];
    }
}
