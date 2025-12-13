<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi Pemeliharaan - {{ $maintenance->AssetMaintenance->assets_number ?? 'N/A' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .logo {
            width: 60px;
            height: auto;
            float: left;
            margin-right: 15px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            color: #1a5490;
        }

        .company-subtitle {
            font-size: 17px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
            color: #1a5490;
        }

        .company-address {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 15px;
            text-transform: uppercase;
            color: #1a5490;
            border-bottom: 2px solid #1a5490;
            padding-bottom: 8px;
        }

        .invoice-info {
            margin-bottom: 15px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .info-label {
            display: table-cell;
            width: 180px;
            font-weight: bold;
            color: #555;
            padding-right: 10px;
        }

        .info-value {
            display: table-cell;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #1a5490;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 5px;
        }

        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        table.detail-table th,
        table.detail-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }

        table.detail-table th {
            background-color: #1a5490;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-ringan {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-sedang {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-berat {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-berkala {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .cost-section {
            margin-top: 20px;
            border: 2px solid #1a5490;
            padding: 15px;
            background-color: #f8f9fa;
        }

        .cost-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .cost-label {
            display: table-cell;
            font-weight: bold;
            font-size: 14px;
            color: #555;
        }

        .cost-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            color: #1a5490;
        }

        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-container {
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 48%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }

        .signature-box.left {
            text-align: left;
        }

        .signature-box.right {
            text-align: right;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #1a5490;
        }

        .signature-space {
            height: 70px;
            margin: 10px 0;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
            margin-top: 5px;
        }

        .signature-name {
            font-weight: bold;
        }

        .signature-position {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .notes-section {
            margin-top: 20px;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .notes-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
        }

        .notes-content {
            font-size: 10px;
            color: #666;
            line-height: 1.5;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .signature-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @php
            $logoPath = public_path('images/logo.png');
        @endphp
        @if(file_exists($logoPath))
        <img src="{{ $logoPath }}" alt="Logo" class="logo">
        @endif
        <div class="header-content">
            <div class="company-name">Pemerintah Kabupaten/Kota</div>
            <div class="company-subtitle">PERUSAHAAN DAERAH AIR MINUM</div>
            <div class="company-address">
                Jl. Alamat Kantor No. XX, Kota, Provinsi | Telp: (0XXX) XXXXXX | Email: info@pdam.go.id
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Judul Kwitansi -->
    <div class="invoice-title">
        KWITANSI PEMELIHARAAN / PERBAIKAN ASET
    </div>

    <!-- Informasi Umum -->
    <div class="invoice-info">
        <div class="info-row">
            <div class="info-label">Nomor Kwitansi</div>
            <div class="info-value">: {{ sprintf('KWT-MAINT-%05d', $maintenance->id) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Pemeliharaan</div>
            <div class="info-value">: {{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d F Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Cetak</div>
            <div class="info-value">: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</div>
        </div>
    </div>

    <!-- Detail Aset -->
    <div class="section-title">DETAIL ASET</div>
    <table class="detail-table">
        <tr>
            <th width="25%">Nomor Aset</th>
            <th width="40%">Nama Aset</th>
            <th width="35%">Jenis Perbaikan</th>
        </tr>
        <tr>
            <td>{{ $maintenance->AssetMaintenance->assets_number ?? 'N/A' }}</td>
            <td>{{ $maintenance->AssetMaintenance->name ?? 'N/A' }}</td>
            <td class="text-center">
                @php
                    $badgeClass = match($maintenance->service_type) {
                        'Perbaikan Ringan' => 'badge-ringan',
                        'Perbaikan Sedang' => 'badge-sedang',
                        'Perbaikan Berat' => 'badge-berat',
                        'Perawatan Berkala' => 'badge-berkala',
                        default => 'badge-ringan'
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $maintenance->service_type }}</span>
            </td>
        </tr>
    </table>

    <!-- Detail Perbaikan -->
    <div class="section-title">DETAIL PERBAIKAN</div>
    <div class="invoice-info">
        <div class="info-row">
            <div class="info-label">Lokasi Service/Tempat Perbaikan</div>
            <div class="info-value">: {{ $maintenance->location_service }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Deskripsi Kerusakan & Perbaikan</div>
            <div class="info-value">: {{ $maintenance->desc ?? '-' }}</div>
        </div>
    </div>

    <!-- Biaya Perbaikan -->
    <div class="cost-section">
        <div class="cost-row">
            <div class="cost-label">TOTAL BIAYA PERBAIKAN</div>
            <div class="cost-value">Rp {{ number_format($maintenance->service_cost, 0, ',', '.') }}</div>
        </div>
        <div style="margin-top: 10px; font-size: 11px; color: #666;">
            <em>Terbilang: {{ ucwords(\App\Helpers\NumberToWords::convert($maintenance->service_cost)) }} Rupiah</em>
        </div>
    </div>

    <!-- Catatan -->
    @if($maintenance->invoice_file)
    <div class="notes-section">
        <div class="notes-title">CATATAN:</div>
        <div class="notes-content">
            • Bukti invoice/struk terlampir dalam sistem<br>
            • Kwitansi ini merupakan bukti sah pengeluaran biaya pemeliharaan aset
        </div>
    </div>
    @endif

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <div class="signature-container">
            <!-- Pihak Tempat Perbaikan -->
            <div class="signature-box left">
                <div class="signature-title">Pihak Tempat Perbaikan</div>
                <div class="signature-space"></div>
                <div class="signature-line">
                    <div class="signature-name">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
                    <div class="signature-position">{{ $maintenance->location_service }}</div>
                </div>
            </div>

            <!-- Kepala Sub Bagian Kerumahtanggaan -->
            <div class="signature-box right">
                <div class="signature-title">Kepala Sub Bagian Kerumahtanggaan</div>
                <div class="signature-space"></div>
                <div class="signature-line">
                    <div class="signature-name">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
                    <div class="signature-position">NIP. ____________________</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dokumen ini dicetak secara otomatis oleh Sistem Manajemen Aset PDAM<br>
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i:s') }} WIB
    </div>
</body>
</html>
