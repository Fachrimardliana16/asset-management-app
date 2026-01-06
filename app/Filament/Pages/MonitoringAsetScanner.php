<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\AssetMonitoring;
use App\Models\AssetMutation;
use App\Models\MasterAssetsCondition;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class MonitoringAsetScanner extends Page implements HasForms
{
    use InteractsWithForms, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Monitoring Barang';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.monitoring-aset-scanner';
    protected static ?string $title = 'Monitoring Barang - Scanner';

    public ?array $data = [];
    public ?Asset $scannedAsset = null;

    #[Url]
    public string $asset = '';

    public string $barcodeInput = '';
    public bool $showAssetInfo = false;
    public bool $assetNeedsAttention = false;
    public array $assetWarnings = [];

    public function mount(): void
    {
        $this->form->fill();

        // Cek jika ada error dari redirect (QR Code scan dengan ID tidak valid)
        if (session()->has('error')) {
            Notification::make()
                ->danger()
                ->title('Data Tidak Ditemukan')
                ->body('Data tidak dapat ditemukan. Periksa lagi Nomor Aset dan QRCode yang tertera.')
                ->persistent()
                ->send();
        }

        // Jika ada parameter asset dari QR Code scan, langsung search
        if (!empty($this->asset)) {
            $this->barcodeInput = $this->asset;
            $this->searchAsset();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Hasil Scan')
                    ->description('Informasi aset yang di-scan')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])->schema([
                            // Kolom Gambar
                            Placeholder::make('asset_image')
                                ->label('Foto Aset')
                                ->content(fn() => new HtmlString(
                                    $this->scannedAsset && $this->scannedAsset->img
                                        ? '<div class="flex justify-center"><img src="' . asset('storage/' . $this->scannedAsset->img) . '" alt="Foto Aset" class="object-cover h-auto max-w-full border border-gray-200 rounded-lg shadow-md max-h-48" /></div>'
                                        : '<div class="flex items-center justify-center h-48 bg-gray-100 border border-gray-300 border-dashed rounded-lg dark:bg-gray-800 dark:border-gray-600"><span class="text-gray-400 dark:text-gray-500">Tidak ada foto</span></div>'
                                ))
                                ->columnSpan(1),

                            // Kolom Info Aset
                            Grid::make(1)->schema([
                                Placeholder::make('asset_number_display')
                                    ->label('Nomor Aset')
                                    ->content(fn() => new HtmlString(
                                        '<span class="text-lg font-bold text-primary-600">' . ($this->scannedAsset?->assets_number ?? '-') . '</span>'
                                    )),
                                Placeholder::make('asset_name_display')
                                    ->label('Nama Aset')
                                    ->content(fn() => $this->scannedAsset?->name ?? '-'),
                                Placeholder::make('category_display')
                                    ->label('Kategori')
                                    ->content(fn() => $this->scannedAsset?->categoryAsset?->name ?? '-'),
                                Placeholder::make('brand_display')
                                    ->label('Merk')
                                    ->content(fn() => $this->scannedAsset?->brand ?? '-'),
                            ])->columnSpan(1),

                            // Kolom Status & Nilai
                            Grid::make(1)->schema([
                                Placeholder::make('current_condition_display')
                                    ->label('Kondisi Saat Ini')
                                    ->content(fn() => new HtmlString(
                                        $this->scannedAsset
                                            ? '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $this->getConditionClass() . '">' . ($this->scannedAsset->conditionAsset?->name ?? '-') . '</span>'
                                            : '-'
                                    )),
                                Placeholder::make('price_display')
                                    ->label('Harga Beli')
                                    ->content(fn() => $this->scannedAsset ? 'Rp ' . number_format($this->scannedAsset->price, 0, ',', '.') : '-'),
                                Placeholder::make('book_value_display')
                                    ->label('Nilai Buku')
                                    ->content(fn() => $this->scannedAsset ? 'Rp ' . number_format($this->scannedAsset->book_value, 0, ',', '.') : '-'),
                                Placeholder::make('book_expiry_display')
                                    ->label('Habis Nilai Buku')
                                    ->content(fn() => new HtmlString(
                                        $this->scannedAsset
                                            ? '<span class="' . ($this->scannedAsset->book_value_expiry <= now() ? 'text-red-600 font-bold' : 'text-green-600') . '">' . $this->scannedAsset->book_value_expiry?->format('d M Y') . '</span>'
                                            : '-'
                                    )),
                            ])->columnSpan(1),
                        ]),
                    ])
                    ->visible(fn() => $this->showAssetInfo),

                // Section Informasi Mutasi/Lokasi
                Section::make('Informasi Lokasi & Pemegang')
                    ->description('Data mutasi terakhir aset')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])->schema([
                            Placeholder::make('mutation_employee')
                                ->label('Pemegang/Penanggung Jawab')
                                ->content(fn() => new HtmlString(
                                    $this->getLatestMutation()
                                        ? '<div class="flex items-center gap-2"><span class="font-semibold">' .
                                        ($this->getLatestMutation()->AssetsMutationemployee?->name ?? 'Tidak ada data') .
                                        '</span></div>'
                                        : '<span class="text-gray-400">Belum ada data mutasi</span>'
                                )),
                            Placeholder::make('mutation_date')
                                ->label('Tanggal Mutasi')
                                ->content(fn() => $this->getLatestMutation()?->mutation_date
                                    ? \Carbon\Carbon::parse($this->getLatestMutation()->mutation_date)->format('d M Y')
                                    : '-'),
                        ]),
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])->schema([
                            Placeholder::make('mutation_location')
                                ->label('Lokasi')
                                ->content(fn() => new HtmlString(
                                    $this->getLatestMutation()
                                        ? '<div class="flex items-center gap-2"><span class="inline-flex items-center justify-center w-8 h-8 text-green-600 bg-green-100 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></span><span class="font-semibold">' . ($this->getLatestMutation()->AssetsMutationlocation?->name ?? 'Tidak ada data') . '</span></div>'
                                        : '<span class="text-gray-400">-</span>'
                                )),
                            Placeholder::make('mutation_sub_location')
                                ->label('Sub Lokasi')
                                ->content(fn() => $this->getLatestMutation()?->AssetsMutationsubLocation?->name ?? '-'),
                        ]),
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])->schema([
                            Placeholder::make('mutation_number')
                                ->label('No. Mutasi')
                                ->content(fn() => new HtmlString(
                                    $this->getLatestMutation()
                                        ? '<code class="px-2 py-1 text-sm bg-gray-100 rounded dark:bg-gray-700">' . $this->getLatestMutation()->mutations_number . '</code>'
                                        : '-'
                                )),
                            Placeholder::make('mutation_status')
                                ->label('Status Transaksi')
                                ->content(fn() => new HtmlString(
                                    $this->getLatestMutation()?->transactionStatus
                                        ? '<span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">' . $this->getLatestMutation()->transactionStatus->name . '</span>'
                                        : '-'
                                )),
                        ]),
                        Placeholder::make('mutation_desc')
                            ->label('Keterangan Mutasi')
                            ->content(fn() => $this->getLatestMutation()?->desc ?? '-'),
                    ])
                    ->visible(fn() => $this->showAssetInfo)
                    ->collapsible()
                    ->collapsed(false),

                Section::make('⚠️ Peringatan')
                    ->description('Aset ini memerlukan perhatian')
                    ->schema([
                        Placeholder::make('warnings')
                            ->label('')
                            ->content(fn() => new HtmlString(
                                '<ul class="space-y-1 list-disc list-inside">' .
                                    collect($this->assetWarnings)->map(fn($w) => "<li class=\"text-red-600\">{$w}</li>")->implode('') .
                                    '</ul>'
                            )),
                    ])
                    ->visible(fn() => $this->assetNeedsAttention && count($this->assetWarnings) > 0)
                    ->extraAttributes(['class' => 'bg-red-50 border-red-200']),

                // Riwayat Mutasi
                Section::make('🔄 Riwayat Mutasi')
                    ->description('Daftar mutasi/perpindahan aset')
                    ->schema([
                        Placeholder::make('mutation_history')
                            ->label('')
                            ->content(fn() => new HtmlString($this->getMutationHistoryHtml())),
                    ])
                    ->visible(fn() => $this->showAssetInfo)
                    ->collapsible()
                    ->collapsed(true),

                // Riwayat Pemeliharaan
                Section::make('🔧 Riwayat Pemeliharaan')
                    ->description('Daftar pemeliharaan/perbaikan aset')
                    ->schema([
                        Placeholder::make('maintenance_history')
                            ->label('')
                            ->content(fn() => new HtmlString($this->getMaintenanceHistoryHtml())),
                    ])
                    ->visible(fn() => $this->showAssetInfo)
                    ->collapsible()
                    ->collapsed(true),

                // Riwayat Monitoring
                Section::make('📋 Riwayat Monitoring')
                    ->description('Log monitoring sebelumnya')
                    ->schema([
                        Placeholder::make('monitoring_history')
                            ->label('')
                            ->content(fn() => new HtmlString($this->getMonitoringHistoryHtml())),
                    ])
                    ->visible(fn() => $this->showAssetInfo)
                    ->collapsible()
                    ->collapsed(true),

                Section::make('Form Monitoring')
                    ->description('Update kondisi aset')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('monitoring_date')
                                ->label('Tanggal Monitoring')
                                ->required()
                                ->default(now()),
                            Select::make('new_condition_id')
                                ->label('Kondisi Baru')
                                ->options(MasterAssetsCondition::pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                        ]),
                        Textarea::make('desc')
                            ->label('Catatan/Keterangan')
                            ->placeholder('Masukkan catatan hasil monitoring...')
                            ->rows(3),
                    ])
                    ->visible(fn() => $this->showAssetInfo),
            ])
            ->statePath('data');
    }

    public function searchAsset(): void
    {
        if (empty($this->barcodeInput)) {
            Notification::make()
                ->warning()
                ->title('Perhatian')
                ->body('Silakan masukkan atau scan nomor aset terlebih dahulu.')
                ->send();
            return;
        }

        $this->scannedAsset = Asset::with(['categoryAsset', 'conditionAsset', 'assetsStatus', 'AssetTransactionStatus'])
            ->where('assets_number', $this->barcodeInput)
            ->first();

        if (!$this->scannedAsset) {
            Notification::make()
                ->danger()
                ->title('Data Tidak Ditemukan')
                ->body('Data tidak dapat ditemukan. Periksa lagi Nomor Aset dan QRCode yang tertera.')
                ->persistent()
                ->send();

            $this->showAssetInfo = false;
            $this->assetNeedsAttention = false;
            $this->assetWarnings = [];
            return;
        }

        $this->showAssetInfo = true;
        $this->checkAssetCondition();

        // Set default condition
        $this->data['new_condition_id'] = $this->scannedAsset->condition_id;

        // Auto record monitoring saat scan/cari aset
        $this->recordMonitoringScan();

        Notification::make()
            ->success()
            ->title('Aset Ditemukan & Tercatat')
            ->body("Aset '{$this->scannedAsset->name}' berhasil ditemukan dan tercatat di riwayat monitoring.")
            ->send();
    }

    /**
     * Record otomatis saat scan/cari aset (sebagai log monitoring)
     */
    private function recordMonitoringScan(): void
    {
        if (!$this->scannedAsset) {
            return;
        }

        try {
            AssetMonitoring::create([
                'monitoring_date' => now()->toDateString(),
                'assets_id' => $this->scannedAsset->id,
                'assets_number' => $this->scannedAsset->assets_number,
                'name' => $this->scannedAsset->name,
                'old_condition_id' => $this->scannedAsset->condition_id,
                'new_condition_id' => $this->scannedAsset->condition_id, // Kondisi sama karena hanya scan
                'desc' => 'Monitoring via scan QR Code / pencarian manual',
                'users_id' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            // Log error tapi jangan tampilkan ke user
            Log::error('Failed to record monitoring scan: ' . $e->getMessage());
        }
    }

    private function checkAssetCondition(): void
    {
        $this->assetWarnings = [];
        $this->assetNeedsAttention = false;

        if (!$this->scannedAsset) return;

        // Cek nilai buku sudah habis
        if ($this->scannedAsset->book_value_expiry <= now()) {
            $this->assetWarnings[] = '📅 Nilai buku sudah habis sejak ' . $this->scannedAsset->book_value_expiry->format('d M Y') . '. Pertimbangkan untuk penggantian aset.';
            $this->assetNeedsAttention = true;
        }

        // Cek kondisi rusak
        $conditionName = strtolower($this->scannedAsset->conditionAsset?->name ?? '');
        if (str_contains($conditionName, 'rusak')) {
            $this->assetWarnings[] = '🔧 Aset dalam kondisi ' . $this->scannedAsset->conditionAsset->name . '. Perlu perbaikan atau penggantian.';
            $this->assetNeedsAttention = true;
        }

        // Cek usia aset (misal lebih dari 5 tahun)
        if ($this->scannedAsset->purchase_date && $this->scannedAsset->purchase_date->diffInYears(now()) >= 5) {
            $years = $this->scannedAsset->purchase_date->diffInYears(now());
            $this->assetWarnings[] = "⏰ Aset sudah berusia {$years} tahun. Periksa kondisi fisik secara menyeluruh.";
            $this->assetNeedsAttention = true;
        }
    }

    private function getConditionClass(): string
    {
        $condition = strtolower($this->scannedAsset?->conditionAsset?->name ?? '');

        if (str_contains($condition, 'baik')) {
            return 'bg-green-100 text-green-800';
        } elseif (str_contains($condition, 'ringan')) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif (str_contains($condition, 'rusak') || str_contains($condition, 'berat')) {
            return 'bg-red-100 text-red-800';
        }

        return 'bg-gray-100 text-gray-800';
    }

    private function getLatestMutation(): ?AssetMutation
    {
        if (!$this->scannedAsset) {
            return null;
        }

        return AssetMutation::with(['AssetsMutationemployee', 'AssetsMutationlocation', 'AssetsMutationsubLocation', 'transactionStatus'])
            ->where('assets_id', $this->scannedAsset->id)
            ->orderBy('mutation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function getMonitoringHistoryHtml(): string
    {
        if (!$this->scannedAsset) {
            return '<p class="text-gray-500">Tidak ada data</p>';
        }

        $histories = AssetMonitoring::with(['MonitoringoldCondition', 'MonitoringNewCondition'])
            ->where('assets_id', $this->scannedAsset->id)
            ->orderBy('monitoring_date', 'desc')
            ->limit(10)
            ->get();

        if ($histories->isEmpty()) {
            return '<p class="py-4 text-center text-gray-500">Belum ada riwayat monitoring untuk aset ini.</p>';
        }

        $html = '<div class="space-y-3">';

        foreach ($histories as $history) {
            $date = $history->monitoring_date ? \Carbon\Carbon::parse($history->monitoring_date)->format('d M Y') : '-';
            $oldCondition = $history->MonitoringoldCondition?->name ?? '-';
            $newCondition = $history->MonitoringNewCondition?->name ?? '-';
            $desc = $history->desc ?? '-';

            // Determine color based on condition change
            $conditionChanged = $history->old_condition_id !== $history->new_condition_id;
            $borderColor = $conditionChanged ? 'border-l-yellow-500' : 'border-l-green-500';

            $html .= "
                <div class=\"bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border-l-4 {$borderColor}\">
                    <div class=\"flex justify-between items-start mb-2\">
                        <span class=\"text-sm font-semibold text-gray-700 dark:text-gray-300\">📅 {$date}</span>
                    </div>
                    <div class=\"grid grid-cols-2 gap-2 text-sm\">
                        <div>
                            <span class=\"text-gray-500\">Kondisi Lama:</span>
                            <span class=\"ml-1 font-medium\">{$oldCondition}</span>
                        </div>
                        <div>
                            <span class=\"text-gray-500\">Kondisi Baru:</span>
                            <span class=\"ml-1 font-medium\">{$newCondition}</span>
                        </div>
                    </div>
                    <div class=\"mt-2 text-sm text-gray-600 dark:text-gray-400\">
                        <span class=\"text-gray-500\">Catatan:</span> {$desc}
                    </div>
                </div>
            ";
        }

        $html .= '</div>';

        return $html;
    }

    private function getMutationHistoryHtml(): string
    {
        if (!$this->scannedAsset) {
            return '<p class="text-gray-500">Tidak ada data</p>';
        }

        $mutations = AssetMutation::with(['AssetsMutationemployee', 'AssetsMutationlocation', 'AssetsMutationsubLocation', 'AssetsMutationtransactionStatus'])
            ->where('assets_id', $this->scannedAsset->id)
            ->orderBy('mutation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($mutations->isEmpty()) {
            return '<p class="py-4 text-center text-gray-500">Belum ada riwayat mutasi untuk aset ini.</p>';
        }

        $html = '<div class="space-y-3">';

        foreach ($mutations as $mutation) {
            $date = $mutation->mutation_date ? \Carbon\Carbon::parse($mutation->mutation_date)->format('d M Y') : '-';
            $transactionType = $mutation->AssetsMutationtransactionStatus?->name ?? '-';
            $employee = $mutation->AssetsMutationemployee?->name ?? '-';
            $location = $mutation->AssetsMutationlocation?->name ?? '-';
            $subLocation = $mutation->AssetsMutationsubLocation?->name ?? '-';
            $mutationNumber = $mutation->mutations_number ?? '-';
            $desc = $mutation->desc ?? '-';

            // Color based on transaction type
            $isKeluar = str_contains(strtolower($transactionType), 'keluar');
            $borderColor = $isKeluar ? 'border-l-red-500' : 'border-l-green-500';
            $badgeClass = $isKeluar ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';

            $html .= "
                <div class=\"bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border-l-4 {$borderColor}\">
                    <div class=\"flex justify-between items-start mb-2\">
                        <span class=\"text-sm font-semibold text-gray-700 dark:text-gray-300\">📅 {$date}</span>
                        <span class=\"px-2 py-1 text-xs font-medium rounded-full {$badgeClass}\">{$transactionType}</span>
                    </div>
                    <div class=\"grid grid-cols-2 gap-2 text-sm mb-2\">
                        <div>
                            <span class=\"text-gray-500\">No. Mutasi:</span>
                            <span class=\"ml-1 font-medium\">{$mutationNumber}</span>
                        </div>
                        <div>
                            <span class=\"text-gray-500\">Pemegang:</span>
                            <span class=\"ml-1 font-medium\">{$employee}</span>
                        </div>
                    </div>
                    <div class=\"grid grid-cols-2 gap-2 text-sm\">
                        <div>
                            <span class=\"text-gray-500\">Lokasi:</span>
                            <span class=\"ml-1 font-medium\">{$location}</span>
                        </div>
                        <div>
                            <span class=\"text-gray-500\">Sub Lokasi:</span>
                            <span class=\"ml-1 font-medium\">{$subLocation}</span>
                        </div>
                    </div>
                    <div class=\"mt-2 text-sm text-gray-600 dark:text-gray-400\">
                        <span class=\"text-gray-500\">Catatan:</span> {$desc}
                    </div>
                </div>
            ";
        }

        $html .= '</div>';

        return $html;
    }

    private function getMaintenanceHistoryHtml(): string
    {
        if (!$this->scannedAsset) {
            return '<p class="text-gray-500">Tidak ada data</p>';
        }

        $maintenances = AssetMaintenance::where('assets_id', $this->scannedAsset->id)
            ->orderBy('maintenance_date', 'desc')
            ->limit(10)
            ->get();

        if ($maintenances->isEmpty()) {
            return '<p class="py-4 text-center text-gray-500">Belum ada riwayat pemeliharaan untuk aset ini.</p>';
        }

        $html = '<div class="space-y-3">';

        foreach ($maintenances as $maintenance) {
            $date = $maintenance->maintenance_date ? \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y') : '-';
            $serviceType = $maintenance->service_type ?? '-';
            $locationService = $maintenance->location_service ?? '-';
            $serviceCost = $maintenance->service_cost ? 'Rp ' . number_format($maintenance->service_cost, 0, ',', '.') : '-';
            $desc = $maintenance->desc ?? '-';

            // Color based on service type
            $borderColor = 'border-l-blue-500';
            if (str_contains(strtolower($serviceType), 'berat') || str_contains(strtolower($serviceType), 'penggantian')) {
                $borderColor = 'border-l-orange-500';
            } elseif (str_contains(strtolower($serviceType), 'rutin')) {
                $borderColor = 'border-l-green-500';
            }

            $html .= "
                <div class=\"bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border-l-4 {$borderColor}\">
                    <div class=\"flex justify-between items-start mb-2\">
                        <span class=\"text-sm font-semibold text-gray-700 dark:text-gray-300\">📅 {$date}</span>
                        <span class=\"px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700\">{$serviceType}</span>
                    </div>
                    <div class=\"grid grid-cols-2 gap-2 text-sm\">
                        <div>
                            <span class=\"text-gray-500\">Lokasi Perbaikan:</span>
                            <span class=\"ml-1 font-medium\">{$locationService}</span>
                        </div>
                        <div>
                            <span class=\"text-gray-500\">Biaya:</span>
                            <span class=\"ml-1 font-medium\">{$serviceCost}</span>
                        </div>
                    </div>
                    <div class=\"mt-2 text-sm text-gray-600 dark:text-gray-400\">
                        <span class=\"text-gray-500\">Catatan:</span> {$desc}
                    </div>
                </div>
            ";
        }

        $html .= '</div>';

        return $html;
    }

    public function saveMonitoring(): void
    {
        if (!$this->scannedAsset) {
            Notification::make()
                ->warning()
                ->title('Perhatian')
                ->body('Silakan scan aset terlebih dahulu.')
                ->send();
            return;
        }

        $data = $this->form->getState();

        if (empty($data['monitoring_date']) || empty($data['new_condition_id'])) {
            Notification::make()
                ->warning()
                ->title('Data Tidak Lengkap')
                ->body('Silakan lengkapi tanggal monitoring dan kondisi baru.')
                ->send();
            return;
        }

        try {
            // Simpan data monitoring ke Riwayat Monitoring
            $monitoring = AssetMonitoring::create([
                'monitoring_date' => $data['monitoring_date'],
                'assets_id' => $this->scannedAsset->id,
                'assets_number' => $this->scannedAsset->assets_number,
                'name' => $this->scannedAsset->name,
                'old_condition_id' => $this->scannedAsset->condition_id,
                'new_condition_id' => $data['new_condition_id'],
                'desc' => $data['desc'] ?? null,
                'users_id' => auth()->id(),
            ]);

            // Update kondisi aset
            $this->scannedAsset->update([
                'condition_id' => $data['new_condition_id'],
            ]);

            Notification::make()
                ->success()
                ->title('Berhasil!')
                ->body('Data monitoring berhasil disimpan ke Riwayat Monitoring (ID: ' . $monitoring->id . ')')
                ->send();

            // Reset form
            $this->reset(['barcodeInput', 'showAssetInfo', 'assetNeedsAttention', 'assetWarnings']);
            $this->scannedAsset = null;
            $this->form->fill();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error!')
                ->body('Gagal menyimpan data: ' . $e->getMessage())
                ->persistent()
                ->send();
        }
    }

    public function resetForm(): void
    {
        $this->reset(['barcodeInput', 'showAssetInfo', 'assetNeedsAttention', 'assetWarnings']);
        $this->scannedAsset = null;
        $this->form->fill();
    }

    #[On('barcode-scanned')]
    public function handleBarcodeScanned(string $barcode): void
    {
        $this->barcodeInput = $barcode;
        $this->searchAsset();
    }
}
