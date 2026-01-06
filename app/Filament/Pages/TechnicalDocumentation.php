<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class TechnicalDocumentation extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Dokumen Aplikasi';
    protected static ?string $navigationLabel = 'Technical Documentation';
    protected static ?string $title = 'Technical Documentation';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationUrl = 'https://docs.google.com/document/d/19wMNkz7oW29tYP5DRw1B4ab1ty61PpwxMdwotJeCRK0/edit?usp=sharing';

    protected static string $view = 'filament.pages.technical-documentation';

    public static function shouldOpenInNewTab(): bool
    {
        return true;
    }
}
