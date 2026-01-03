<?php

namespace App\Filament\Resources\AssetTaxResource\Pages;

use App\Filament\Resources\AssetTaxResource;
use App\Filament\Exports\AssetTaxExporter;
use App\Filament\Imports\AssetTaxImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssetTaxes extends ListRecords
{
    protected static string $resource = AssetTaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Hapus CreateAction - resource ini hanya untuk histori
            // Actions\CreateAction::make(),

            Actions\ExportAction::make()
                ->exporter(AssetTaxExporter::class)
                ->label('Export Laporan Pajak')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),

            // Hapus ImportAction - input pajak dilakukan dari AssetResource
            // Actions\ImportAction::make()
            //     ->importer(AssetTaxImporter::class)
            //     ->label('Import')
            //     ->icon('heroicon-o-arrow-up-tray')
            //     ->color('info'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn() => $this->getModel()::count()),

            'pending_approval' => Tab::make('Menunggu Approval')
                ->modifyQueryUsing(fn(Builder $query) => $query->pendingApproval())
                ->badge(fn() => $this->getModel()::pendingApproval()->count())
                ->badgeColor('warning'),

            'unpaid' => Tab::make('Belum Dibayar')
                ->modifyQueryUsing(fn(Builder $query) => $query->unpaid())
                ->badge(fn() => $this->getModel()::unpaid()->count())
                ->badgeColor('danger'),

            'overdue' => Tab::make('Terlambat')
                ->modifyQueryUsing(fn(Builder $query) => $query->overdue())
                ->badge(fn() => $this->getModel()::overdue()->count())
                ->badgeColor('danger'),

            'upcoming' => Tab::make('Akan Jatuh Tempo')
                ->modifyQueryUsing(fn(Builder $query) => $query->upcoming(30))
                ->badge(fn() => $this->getModel()::upcoming(30)->count())
                ->badgeColor('warning'),

            'paid' => Tab::make('Sudah Dibayar')
                ->modifyQueryUsing(fn(Builder $query) => $query->paid())
                ->badge(fn() => $this->getModel()::paid()->count())
                ->badgeColor('success'),
        ];
    }
}
