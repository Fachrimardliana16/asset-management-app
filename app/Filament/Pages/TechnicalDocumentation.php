<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;

class TechnicalDocumentation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Dokumen Aplikasi';
    protected static ?string $navigationLabel = 'Technical Documentation';
    protected static ?string $title = 'Technical Documentation';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.technical-documentation';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->sort(static::getNavigationSort())
                ->url('https://docs.google.com/document/d/19wMNkz7oW29tYP5DRw1B4ab1ty61PpwxMdwotJeCRK0/edit?usp=sharing')
                ->openUrlInNewTab(),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
