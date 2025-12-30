<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetPurchaseResource\Pages;
use App\Filament\Resources\AssetPurchaseResource\RelationManagers;
use App\Models\AssetRequests;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;

class AssetPurchaseResource extends Resource
{
    protected static ?string $model = AssetRequests::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Pembelian Barang';
    protected static ?string $modelLabel = 'Pembelian Barang';
    protected static ?string $pluralModelLabel = 'Pembelian Barang';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Permintaan Barang')
                    ->description('Data Permintaan yang akan diproses')
                    ->schema([
                        Forms\Components\TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->disabled(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Permintaan')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_items')
                            ->label('Total Jenis Barang')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_quantity')
                            ->label('Total Unit')
                            ->disabled(),
                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'items.category',
                'items.location', 
                'items.subLocation',
                'items.purchases',
                'department',
                'requestedBy',
                'purchases.condition',
                'purchases.status'
            ]))
            ->columns([
                // 1. No. DBP & Tanggal
                TextColumn::make('document_number')
                    ->label('Info DPB')
                    ->description(fn($record) => $record->date->format('d M Y'))
                    ->searchable()
                    ->sortable(),

                // 2. Items Summary (Multiple Items)
                TextColumn::make('items_summary')
                    ->label('Detail Barang')
                    ->html()
                    ->state(function ($record) {
                        // Gunakan eager loaded items
                        $items = $record->items;
                        
                        if (!$items || $items->isEmpty()) {
                            return '<span class="text-gray-500 italic text-sm">Belum ada item</span>';
                        }

                        $summary = '<div class="space-y-2">';
                        
                        foreach ($items->take(3) as $item) {
                            $categoryName = $item->category?->name ?? '-';
                            $summary .= '<div class="flex flex-col border-l-2 border-primary-500 pl-2 py-1">';
                            $summary .= '<div class="font-semibold text-sm" style="color: inherit;">' . e($item->asset_name) . '</div>';
                            $summary .= '<div class="flex items-center gap-1.5 text-xs">';
                            $summary .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-800">' . e($categoryName) . '</span>';
                            $summary .= '<span style="color: inherit;">•</span>';
                            $summary .= '<span class="font-medium text-success-600">' . e($item->quantity) . ' unit</span>';
                            $summary .= '</div>';
                            $summary .= '</div>';
                        }
                        
                        if ($items->count() > 3) {
                            $more = $items->count() - 3;
                            $summary .= '<div class="text-xs text-primary-600 font-medium pl-2 pt-1">+ ' . $more . ' item lainnya...</div>';
                        }
                        
                        $summary .= '</div>';

                        return $summary;
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->wrap(),

                // 3. Department & Pemohon
                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('requestedBy.name')
                    ->label('Pemohon')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                // 4. Total Items & Quantity
                Tables\Columns\TextColumn::make('total_items')
                    ->label('Total')
                    ->html()
                    ->formatStateUsing(fn($record) => new HtmlString(
                        "<div class='font-medium'>{$record->total_items} jenis</div>" .
                            "<div class='text-sm text-gray-600'>{$record->total_quantity} unit</div>"
                    ))
                    ->alignCenter()
                    ->sortable(),

                // 5. Progress Pembelian
                TextColumn::make('purchase_progress')
                    ->label('Progress')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $totalQty = $record->total_quantity;
                        $purchasedQty = $record->purchases()->count();
                        $percentage = $totalQty > 0 ? min(100, ($purchasedQty / $totalQty) * 100) : 0;
                        
                        $color = $percentage === 0 ? 'gray' : ($percentage === 100 ? 'green' : 'blue');
                        
                        return new HtmlString(
                            "<div class='flex items-center gap-2'>" .
                                "<div class='flex-1 bg-gray-200 rounded-full h-2'>" .
                                    "<div class='bg-{$color}-600 h-2 rounded-full' style='width: {$percentage}%'></div>" .
                                "</div>" .
                                "<span class='text-xs font-medium'>{$purchasedQty}/{$totalQty}</span>" .
                            "</div>"
                        );
                    }),

                Tables\Columns\TextColumn::make('purchase_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'purchased' => 'Sudah Dibeli',
                        'cancelled' => 'Dibatalkan',
                        default => 'Menunggu',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'purchased' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\ImageColumn::make('docs')
                    ->label('Lampiran')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Departemen')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('purchase_status')
                    ->label('Status Pembelian')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'purchased' => 'Sudah Dibeli',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    // 1. Action Utama: Proses Pembelian (Full Page)
                    Tables\Actions\Action::make('proses_pembelian')
                        ->label('Proses Pembelian')
                        ->icon('heroicon-o-shopping-cart')
                        ->color('success')
                        ->visible(fn(AssetRequests $record) => $record->purchase_status !== 'purchased' && $record->items()->count() > 0)
                        ->url(fn(AssetRequests $record) => static::getUrl('process', ['record' => $record->id])),

                    // 2. Action Lain: View
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),

                    // 2b. Action: Cetak Faktur (hanya jika sudah dibeli)
                    Tables\Actions\Action::make('cetak_faktur')
                        ->label('Cetak Faktur')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->visible(fn(AssetRequests $record) => $record->purchase_status === 'purchased')
                        ->url(fn(AssetRequests $record) => route('purchase.invoice', ['record' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\EditAction::make()
                        ->label('Edit Permintaan')
                        ->visible(fn() => in_array(Auth::user()->role, ['super_admin', 'admin', 'kabag', 'kasubag'])),

                    // 4. Action Lain: Hapus (optional)
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation(),
                ])
                    ->label('Action')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('primary')
                    ->button() // jadi tombol bulat rapi
                    ->size('md'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada permintaan barang')
            ->emptyStateDescription('Data permintaan akan muncul setelah ada permintaan barang yang diinput.')
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetPurchases::route('/'),
            'view' => Pages\ViewAssetPurchase::route('/{record}'),
            'process' => Pages\ProcessPurchase::route('/{record}/process'),
        ];
    }
}
