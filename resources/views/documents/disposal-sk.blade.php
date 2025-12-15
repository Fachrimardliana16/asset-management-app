<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keputusan Penghapusan - {{ $disposal->disposals_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 20px;
        }
        .logo {
            width: 80px;
            height: auto;
            float: left;
            margin-right: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            color: #1a5490;
        }
        .company-subtitle {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
            color: #1a5490;
        }
        .company-address {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 25px 0 10px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .document-number {
            font-size: 12px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .opening-text {
            text-align: justify;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        .consideration-section {
            margin-bottom: 20px;
        }
        .consideration-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .consideration-list {
            margin-left: 40px;
            line-height: 1.8;
        }
        .consideration-list li {
            margin-bottom: 8px;
            text-align: justify;
        }
        .decision-section {
            margin: 30px 0;
        }
        .decision-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .decision-content {
            margin-left: 40px;
            line-height: 1.8;
        }
        .asset-detail-box {
            border: 2px solid #1a5490;
            padding: 15px;
            margin: 20px 0;
            background-color: #f8f9fa;
        }
        .asset-detail-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .asset-detail-label {
            display: table-cell;
            width: 200px;
            font-weight: bold;
            color: #555;
        }
        .asset-detail-value {
            display: table-cell;
        }
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signature-container {
            width: 100%;
            margin-top: 30px;
            text-align: justify;
        }
        .signature-box {
            text-align: center;
            vertical-align: top;
        }
        .signature-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
            line-height: 1.4;
        }
        .signature-space {
            height: 70px;
            margin: 10px 0;
        }
        .signature-name {
            font-weight: bold;
            display: inline-block;
            padding: 0 30px;
            margin-bottom: 3px;
        }
        .signature-position {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        .footer-note {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            font-style: italic;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            clear: both;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="clearfix header">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
        @endif
        <div>
            <p class="company-name">Perusahaan Umum Daerah Air Minum</p>
            <p class="company-subtitle">Tirta Perwira Kabupaten Purbalingga</p>
            <p class="company-address">Jl. Letjen S. Parman No.1, Purbalingga</p>
        </div>
    </div>

    <!-- Judul Dokumen -->
    <div class="document-title">
        SURAT KEPUTUSAN PENGHAPUSAN ASET
    </div>
    <div class="document-number">
        Nomor: {{ $disposal->disposals_number }}
    </div>

    <!-- Pembukaan -->
    <div class="opening-text">
        Berdasarkan hasil evaluasi dan pertimbangan yang matang terkait kondisi aset, maka dengan ini ditetapkan keputusan penghapusan aset sebagai berikut:
    </div>

    <!-- Detail Aset yang Dihapus -->
    <div class="asset-detail-box">
        <div style="font-weight: bold; font-size: 14px; margin-bottom: 15px; color: #1a5490; text-align: center;">
            DETAIL ASET YANG DIHAPUS
        </div>
        <div class="asset-detail-row">
            <div class="asset-detail-label">Nomor Aset</div>
            <div class="asset-detail-value">: {{ $disposal->assetDisposals->assets_number }}</div>
        </div>
        <div class="asset-detail-row">
            <div class="asset-detail-label">Nama Aset</div>
            <div class="asset-detail-value">: {{ $disposal->assetDisposals->name }}</div>
        </div>
        <div class="asset-detail-row">
            <div class="asset-detail-label">Kategori</div>
            <div class="asset-detail-value">: {{ $disposal->assetDisposals->categoryAsset->name ?? '-' }}</div>
        </div>
        <div class="asset-detail-row">
            <div class="asset-detail-label">Nilai Buku</div>
            <div class="asset-detail-value">: Rp {{ number_format($disposal->book_value, 0, ',', '.') }}</div>
        </div>
        <div class="asset-detail-row">
            <div class="asset-detail-label">Nilai Penghapusan</div>
            <div class="asset-detail-value">: Rp {{ number_format($disposal->disposal_value, 0, ',', '.') }}</div>
        </div>
        <div class="asset-detail-row">
            <div class="asset-detail-label">Tanggal Penghapusan</div>
            <div class="asset-detail-value">: {{ \Carbon\Carbon::parse($disposal->disposal_date)->format('d F Y') }}</div>
        </div>
    </div>

    <!-- Pertimbangan -->
    <div class="consideration-section">
        <div class="consideration-title">MENIMBANG:</div>
        <ol class="consideration-list">
            <li>Bahwa aset tersebut di atas {{ strtolower($disposal->disposal_reason) }}</li>
            <li>Bahwa penghapusan aset diperlukan untuk efisiensi pengelolaan aset dan administrasi perusahaan</li>
            <li>Bahwa penghapusan aset telah sesuai dengan prosedur dan peraturan yang berlaku</li>
        </ol>
    </div>

    <!-- Keputusan -->
    <div class="decision-section">
        <div class="decision-title">MEMUTUSKAN:</div>
        <div class="decision-content">
            <strong>MENETAPKAN:</strong>
            <ol style="margin-top: 10px;">
                <li>Menghapus aset dengan nomor <strong>{{ $disposal->assetDisposals->assets_number }}</strong> dari daftar inventaris perusahaan.</li>
                <li>Proses penghapusan dilakukan dengan cara: <strong>{{ ucwords($disposal->disposal_process) }}</strong>.</li>
                <li>Status aset berubah dari Active menjadi Inactive terhitung sejak tanggal keputusan ini.</li>
                <li>Surat Keputusan ini berlaku sejak tanggal ditetapkan.</li>
            </ol>
        </div>
    </div>

    @if($disposal->disposal_notes)
    <div style="margin: 20px 0; padding: 10px; background-color: #fff3cd; border-left: 4px solid #ffc107;">
        <strong>Catatan:</strong><br>
        {{ $disposal->disposal_notes }}
    </div>
    @endif

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <div style="text-align: right; margin-bottom: 5px;">
            Ditetapkan di Purbalingga<br>
            Pada tanggal {{ \Carbon\Carbon::parse($disposal->disposal_date)->format('d F Y') }}
        </div>

        <!-- Petugas dan Kepala Sub Bagian (Sejajar) -->
        <div class="signature-container">
            <!-- Petugas / Pembuat -->
            <div class="signature-box" style="width: 48%; display: inline-block; vertical-align: top;">
                <div class="signature-title"><br>PETUGAS</div>
                <div class="signature-space"></div>
                <div class="signature-name">({{ $disposal->petugas->name ?? '........................' }})</div>
                <!-- <div class="signature-position">{{ $disposal->petugas->position->name ?? 'Petugas Administrasi' }}</div> -->
            </div>

            <!-- Kepala Sub Bagian Kerumahtanggaan -->
            <div class="signature-box" style="width: 48%; display: inline-block; vertical-align: top;">
                <div class="signature-title">DISETUJUI OLEH<br>KEPALA SUB BAGIAN KERUMAHTANGGAAN</div>
                <div class="signature-space"></div>
                <div class="signature-name">({{ $disposal->kepalaSubBagian->name ?? '........................' }})</div>
                <!-- <div class="signature-position">{{ $disposal->kepalaSubBagian->position->name ?? 'Kepala Sub Bagian Kerumahtanggaan' }}</div> -->
            </div>
        </div>

        <!-- Direktur (Mengetahui - Di Bawah) -->
        <div style="text-align: center; margin-top: 40px;">
            <div class="signature-box" style="width: 48%; display: inline-block;">
                <div class="signature-title">MENGETAHUI<br>DIREKTUR</div>
                <div class="signature-space"></div>
                <div class="signature-name">({{ $disposal->direktur->name ?? '........................' }})</div>
                <!-- <div class="signature-position">{{ $disposal->direktur->position->name ?? 'Direktur PDAM' }}</div> -->
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
        <strong>Catatan:</strong> Dokumen ini dicetak secara otomatis melalui sistem pada {{ $printed_at->format('d F Y H:i:s') }} WIB.<br>
        Dokumen ini sah sebagai Surat Keputusan Penghapusan Aset dan menjadi dasar perubahan status aset dalam sistem inventaris.
    </div>
</body>
</html>
