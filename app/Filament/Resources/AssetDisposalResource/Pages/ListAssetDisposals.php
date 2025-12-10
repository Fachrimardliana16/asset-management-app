<?php

namespace App\Filament\Resources\AssetDisposalResource\Pages;

use App\Filament\Resources\AssetDisposalResource;
use App\Filament\Resources\AssetDisposalResource\Widgets\AssetDisposalStatsWidget;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListAssetDisposals extends ListRecords
{
    protected static string $resource = AssetDisposalResource::class;

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
                    Select::make('disposal_process')
                        ->label('Filter Proses Penghapusan')
                        ->options([
                            'dimusnahkan' => 'Dimusnahkan',
                            'dijual' => 'Dijual',
                            'dihibahkan' => 'Dihibahkan',
                            'dihapus dari inventaris' => 'Dihapus dari Daftar Inventaris',
                        ])
                        ->placeholder('Semua Proses'),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'disposal_process' => $data['disposal_process'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset-disposal') . '?' . $params);
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetDisposalStatsWidget::class,
        ];
    }
}
