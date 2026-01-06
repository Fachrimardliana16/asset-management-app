<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ManualBook extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Dokumen Aplikasi';
    protected static ?string $navigationLabel = 'Manual Book';
    protected static ?string $title = 'Manual Book';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationUrl = 'https://docs.google.com/document/d/1BMDEoW551PPhgyGZmNzcLY_TCbe0kNCyhbjQiEfXbM8/edit?usp=sharing';

    protected static string $view = 'filament.pages.manual-book';

    public static function shouldOpenInNewTab(): bool
    {
        return true;
    }
}
