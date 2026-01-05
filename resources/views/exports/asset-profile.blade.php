<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Profil Aset - {{ $asset->assets_number }}</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        /* ===== HEADER ===== */
        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            border: none;
        }

        .logo {
            width: 60px;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .company-subtitle {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0 0;
        }

        .company-address {
            font-size: 9px;
            margin-top: 3px;
            line-height: 1.3;
        }

        .header-line {
            border-top: 3px double #000;
            margin: 10px 0 14px;
        }

        /* ===== TITLE ===== */
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        /* ===== SECTION ===== */
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #f2f2f2;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #3b82f6;
        }

        .section-description {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }

        /* ===== QR CODE ===== */
        .qr-container {
            text-align: center;
            padding: 15px 0;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }

        /* ===== INFO TABLE ===== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .info-table td.label {
            width: 35%;
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .info-table td.value {
            width: 65%;
        }

        /* ===== DATA TABLE ===== */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }

        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .data-table td {
            font-size: 9px;
        }

        /* ===== BADGE ===== */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-primary {
            background-color: #cfe2ff;
            color: #084298;
        }

        .badge-gray {
            background-color: #e2e3e5;
            color: #383d41;
        }

        /* ===== UTILITIES ===== */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        /* ===== PAGE BREAK ===== */
        .page-break {
            page-break-after: always;
        }

        .asset-image {
            max-width: 150px;
            max-height: 150px;
            border: 1px solid #ddd;
            padding: 5px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <table>
            <tr>
                <td width="70" align="center">
                    @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
                    @endif
                </td>
                <td align="center">
                    <p class="company-name">Perusahaan Umum Daerah Air Minum</p>
                    <p class="company-subtitle">Tirta Perwira Kabupaten Purbalingga</p>
                    <p class="company-address">
                        Jl. Letnan Jenderal S. Parman No.62, Kedung Menjangan, Bancar,
                        Kec. Purbalingga, Kabupaten Purbalingga, Jawa Tengah 53316
                    </p>
                </td>
            </tr>
        </table>
        <div class="header-line"></div>
    </div>

    <!-- TITLE -->
    <p class="report-title">Profil Aset</p>

    <!-- QR CODE SECTION -->
    @if(in_array('qr_code', $selectedSections) && $qrCode)
    <div class="section">
        <div class="section-title">
            QR Code Aset
            <div class="section-description">QR Code untuk identifikasi aset</div>
        </div>
        <div class="qr-container">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" class="qr-code" alt="QR Code">
            <div style="margin-top: 10px; font-weight: bold; font-size: 11px;">{{ $asset->assets_number }}</div>
        </div>
    </div>
    @endif

    <!-- ASSET INFO SECTION -->
    @if(in_array('asset_info', $selectedSections))
    <div class="section">
        <div class="section-title">
            Informasi Aset
            <div class="section-description">Detail informasi aset</div>
        </div>

        <table class="info-table">
            @if($asset->img && file_exists(public_path('storage/' . $asset->img)))
            <tr>
                <td class="label">Gambar Aset</td>
                <td class="value text-center">
                    <img src="{{ public_path('storage/' . $asset->img) }}" class="asset-image" alt="Gambar Aset">
                </td>
            </tr>
            @endif
            <tr>
                <td class="label">Nomor Aset</td>
                <td class="value"><strong>{{ $asset->assets_number }}</strong></td>
            </tr>
            <tr>
                <td class="label">Nama Aset</td>
                <td class="value"><strong>{{ $asset->name }}</strong></td>
            </tr>
            <tr>
                <td class="label">Kategori</td>
                <td class="value">{{ $asset->categoryAsset->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Merk</td>
                <td class="value">{{ $asset->brand ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status Aset</td>
                <td class="value">
                    @if($asset->assetsStatus)
                    <span class="badge {{ $asset->assetsStatus->name === 'Aktif' ? 'badge-success' : 'badge-danger' }}">
                        {{ $asset->assetsStatus->name }}
                    </span>
                    @else
                    -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Kondisi</td>
                <td class="value">
                    @if($asset->conditionAsset)
                    <span class="badge
                        @if($asset->conditionAsset->name === 'Baik') badge-success
                        @elseif($asset->conditionAsset->name === 'Rusak Ringan') badge-warning
                        @elseif($asset->conditionAsset->name === 'Rusak Berat') badge-danger
                        @else badge-gray
                        @endif">
                        {{ $asset->conditionAsset->name }}
                    </span>
                    @else
                    -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Status Mutasi</td>
                <td class="value">
                    @if($asset->AssetTransactionStatus)
                    <span class="badge {{ $asset->AssetTransactionStatus->name === 'Transaksi Keluar' ? 'badge-danger' : 'badge-success' }}">
                        {{ $asset->AssetTransactionStatus->name }}
                    </span>
                    @else
                    <span class="badge badge-gray">Di Gudang</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Tanggal Pembelian</td>
                <td class="value">{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('d F Y') : '-' }}</td>
            </tr>
            @if($asset->desc)
            <tr>
                <td class="label">Deskripsi</td>
                <td class="value">{{ $asset->desc }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <!-- FINANCIAL INFO SECTION -->
    @if(in_array('financial_info', $selectedSections))
    <div class="section">
        <div class="section-title">
            Informasi Keuangan
            <div class="section-description">Detail nilai dan sumber dana aset</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Harga Beli</td>
                <td class="value">Rp {{ number_format($asset->price, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Nilai Buku</td>
                <td class="value">Rp {{ number_format($asset->book_value, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Habis Nilai Buku</td>
                <td class="value">
                    {{ $asset->book_value_expiry ? \Carbon\Carbon::parse($asset->book_value_expiry)->format('d F Y') : '-' }}
                    @if($asset->book_value_expiry)
                    <span class="badge {{ $asset->book_value_expiry <= now() ? 'badge-danger' : 'badge-success' }}">
                        {{ $asset->book_value_expiry <= now() ? 'Sudah Habis' : 'Masih Berlaku' }}
                    </span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Sumber Dana</td>
                <td class="value">{{ $asset->funding_source ?? '-' }}</td>
            </tr>
        </table>
    </div>
    @endif

    <!-- MUTATIONS SECTION -->
    @if(in_array('mutations', $selectedSections))
    <div class="section">
        <div class="section-title">
            Riwayat Mutasi
            <div class="section-description">Data mutasi barang</div>
        </div>

        @if($asset->AssetsMutation && $asset->AssetsMutation->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="15%">Jenis Mutasi</th>
                    <th width="20%">Pemegang</th>
                    <th width="20%">Lokasi</th>
                    <th width="25%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asset->AssetsMutation as $index => $mutation)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $mutation->mutation_date ? \Carbon\Carbon::parse($mutation->mutation_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        @if($mutation->AssetsMutationtransactionStatus)
                        <span class="badge {{ $mutation->AssetsMutationtransactionStatus->name === 'Transaksi Keluar' ? 'badge-danger' : 'badge-success' }}">
                            {{ $mutation->AssetsMutationtransactionStatus->name }}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $mutation->AssetsMutationemployee->name ?? '-' }}</td>
                    <td>{{ $mutation->AssetsMutationlocation->name ?? '-' }}</td>
                    <td>{{ $mutation->desc ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">Belum ada riwayat mutasi</div>
        @endif
    </div>
    @endif

    <!-- MONITORING SECTION -->
    @if(in_array('monitoring', $selectedSections))
    <div class="section">
        <div class="section-title">
            Riwayat Monitoring
            <div class="section-description">Daftar monitoring/pengecekan aset</div>
        </div>

        @if($asset->assetMonitoring && $asset->assetMonitoring->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="15%">Kondisi Lama</th>
                    <th width="15%">Kondisi Baru</th>
                    <th width="15%">Petugas</th>
                    <th width="35%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asset->assetMonitoring as $index => $monitoring)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $monitoring->monitoring_date ? \Carbon\Carbon::parse($monitoring->monitoring_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        @if($monitoring->MonitoringoldCondition)
                        <span class="badge
                            @if($monitoring->MonitoringoldCondition->name === 'Baik') badge-success
                            @elseif($monitoring->MonitoringoldCondition->name === 'Rusak Ringan') badge-warning
                            @elseif($monitoring->MonitoringoldCondition->name === 'Rusak Berat') badge-danger
                            @else badge-gray
                            @endif">
                            {{ $monitoring->MonitoringoldCondition->name }}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($monitoring->MonitoringNewCondition)
                        <span class="badge
                            @if($monitoring->MonitoringNewCondition->name === 'Baik') badge-success
                            @elseif($monitoring->MonitoringNewCondition->name === 'Rusak Ringan') badge-warning
                            @elseif($monitoring->MonitoringNewCondition->name === 'Rusak Berat') badge-danger
                            @else badge-gray
                            @endif">
                            {{ $monitoring->MonitoringNewCondition->name }}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $monitoring->user->name ?? '-' }}</td>
                    <td>{{ $monitoring->desc ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">Belum ada riwayat monitoring</div>
        @endif
    </div>
    @endif

    <!-- MAINTENANCE SECTION -->
    @if(in_array('maintenance', $selectedSections))
    <div class="section">
        <div class="section-title">
            Riwayat Pemeliharaan
            <div class="section-description">Daftar pemeliharaan/perbaikan aset</div>
        </div>

        @if($asset->AssetMaintenance && $asset->AssetMaintenance->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Tanggal</th>
                    <th width="18%">Jenis Perbaikan</th>
                    <th width="20%">Lokasi Perbaikan</th>
                    <th width="15%">Biaya</th>
                    <th width="30%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asset->AssetMaintenance as $index => $maintenance)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $maintenance->maintenance_date ? \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        <span class="badge
                            @if($maintenance->service_type === 'Perbaikan Ringan') badge-info
                            @elseif($maintenance->service_type === 'Perbaikan Berat') badge-warning
                            @elseif($maintenance->service_type === 'Penggantian Komponen') badge-danger
                            @elseif($maintenance->service_type === 'Perawatan Rutin') badge-success
                            @else badge-gray
                            @endif">
                            {{ $maintenance->service_type }}
                        </span>
                    </td>
                    <td>{{ $maintenance->location_service ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($maintenance->service_cost ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $maintenance->desc ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">Belum ada riwayat pemeliharaan</div>
        @endif
    </div>
    @endif

    <!-- TAXES SECTION -->
    @if(in_array('taxes', $selectedSections))
    <div class="section">
        <div class="section-title">
            Riwayat Pembayaran Pajak
            <div class="section-description">Riwayat pembayaran pajak aset</div>
        </div>

        @if($asset->taxes && $asset->taxes->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="18%">Jenis Pajak</th>
                    <th width="8%">Tahun</th>
                    <th width="14%">Nilai Pajak</th>
                    <th width="10%">Denda</th>
                    <th width="12%">Jatuh Tempo</th>
                    <th width="12%">Tgl Bayar</th>
                    <th width="10%">Status</th>
                    <th width="11%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asset->taxes as $index => $tax)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $tax->taxType->name ?? '-' }}</td>
                    <td class="text-center">{{ $tax->tax_year ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($tax->tax_amount ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($tax->penalty_amount ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $tax->due_date ? \Carbon\Carbon::parse($tax->due_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $tax->payment_date ? \Carbon\Carbon::parse($tax->payment_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        <span class="badge
                            @if($tax->payment_status === 'paid') badge-success
                            @elseif($tax->payment_status === 'pending') badge-warning
                            @elseif($tax->payment_status === 'overdue') badge-danger
                            @else badge-gray
                            @endif">
                            @if($tax->payment_status === 'paid') Lunas
                            @elseif($tax->payment_status === 'pending') Pending
                            @elseif($tax->payment_status === 'overdue') Terlambat
                            @else Batal
                            @endif
                        </span>
                    </td>
                    <td class="text-right"><strong>Rp {{ number_format(($tax->tax_amount ?? 0) + ($tax->penalty_amount ?? 0), 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">Belum ada histori pajak</div>
        @endif
    </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WIB</p>
        <p>Perusahaan Umum Daerah Air Minum Tirta Perwira Kabupaten Purbalingga</p>
    </div>

</body>

</html>
