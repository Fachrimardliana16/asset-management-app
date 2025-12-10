<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;

class ManualBook extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Dokumen Aplikasi';
    protected static ?string $navigationLabel = 'Manual Book';
    protected static ?string $title = 'Manual Book';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manual-book';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->sort(static::getNavigationSort())
                ->url('https://docs.google.com/document/d/1BMDEoW551PPhgyGZmNzcLY_TCbe0kNCyhbjQiEfXbM8/edit?usp=sharing')
                ->openUrlInNewTab(),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
