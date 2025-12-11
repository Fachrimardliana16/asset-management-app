<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dokumen Serah Terima - {{ $mutation->mutations_number }}</title>
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
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 25px 0 10px;
            text-transform: uppercase;
            color: #1a5490;
            border-bottom: 2px solid #1a5490;
            padding-bottom: 10px;
        }
        .document-subtitle {
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
            color: #666;
            font-style: italic;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .info-label {
            display: table-cell;
            width: 180px;
            font-weight: bold;
            color: #555;
        }
        .info-value {
            display: table-cell;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #1a5490;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #1a5490;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-keluar {
            background-color: #ffe5e5;
            color: #c41e1e;
        }
        .badge-masuk {
            background-color: #d4edda;
            color: #155724;
        }
        .signature-section {
            margin-top: 60px;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
            margin-bottom: 20px;
        }
        .signature-box.left {
            float: left;
        }
        .signature-box.right {
            float: right;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }
        .note-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #1a5490;
            clear: both;
        }
        .note-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #1a5490;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            clear: both;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
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

    <div class="document-title">
        Berita Acara Serah Terima Aset
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">No. Berita Acara</div>
            <div class="info-value">: <strong>{{ $mutation->mutations_number }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Mutasi</div>
            <div class="info-value">: {{ \Carbon\Carbon::parse($mutation->mutation_date)->format('d F Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Jenis Transaksi</div>
            <div class="info-value">:
                <span class="badge @if($isMutasiKeluar) badge-keluar @else badge-masuk @endif">
                    {{ $transactionType }}
                </span>
            </div>
        </div>
    </div>

    <div class="section-title">Detail Aset</div>
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Nomor Aset</div>
            <div class="info-value">: <strong>{{ $mutation->assets_number }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Nama Aset</div>
            <div class="info-value">: {{ $mutation->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Kondisi Aset</div>
            <div class="info-value">: {{ $mutation->MutationCondition?->name ?? '-' }}</div>
        </div>
        @if($mutation->AssetsMutation)
        <div class="info-row">
            <div class="info-label">Kategori</div>
            <div class="info-value">: {{ $mutation->AssetsMutation?->category?->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Brand/Tipe</div>
            <div class="info-value">: {{ $mutation->AssetsMutation?->brand ?? '-' }}</div>
        </div>
        @endif
    </div>

    <div class="section-title">
        @if($isMutasiKeluar)
            Kepada Pemegang
        @else
            Dari Pemegang
        @endif
    </div>
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Nama Pemegang</div>
            <div class="info-value">: {{ $mutation->AssetsMutationemployee?->name ?? '-' }}</div>
        </div>
        @if($mutation->AssetsMutationemployee)
        <div class="info-row">
            <div class="info-label">Jabatan</div>
            <div class="info-value">: {{ $mutation->AssetsMutationemployee?->position?->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Bagian</div>
            <div class="info-value">: {{ $mutation->AssetsMutationemployee?->department?->name ?? '-' }}</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Lokasi/Ruang</div>
            <div class="info-value">: {{ $mutation->AssetsMutationlocation?->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Sub Lokasi</div>
            <div class="info-value">: {{ $mutation->AssetsMutationsubLocation?->name ?? '-' }}</div>
        </div>
    </div>

    <div class="section-title">Keterangan</div>
    <div class="info-section">
        @if($mutation->desc)
            <div style="padding: 10px; background-color: #f9f9f9; border-left: 3px solid #1a5490;">
                {{ $mutation->desc }}
            </div>
        @else
            <div style="color: #999; font-style: italic;">Tidak ada keterangan tambahan</div>
        @endif
    </div>

    <div class="clearfix signature-section">
        <div class="signature-box left">
            <div>Mengetahui,</div>
            <div><strong>Kepala Sub Bagian Kerumahtanggan</strong></div>
            <div class="signature-line">
                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
            </div>
        </div>

        <div class="signature-box right">
            <div>Purbalingga, {{ \Carbon\Carbon::parse($printed_at)->format('d F Y') }}</div>
            <div><strong>
                @if($isMutasiKeluar)
                    Petugas Penyerah / Penerima
                @else
                    Petugas Penerima / Penyerah
                @endif
            </strong></div>
            <div class="signature-line">
                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
            </div>
        </div>
    </div>

    @if($mutation->scan_doc)
    <div class="note-section" style="margin-top: 80px;">
        <div class="note-title">Catatan:</div>
        <div>Dokumen pendukung telah disimpan dalam sistem. Scan dokumen berita acara dapat diakses melalui sistem database.</div>
    </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis pada {{ \Carbon\Carbon::parse($printed_at)->format('d F Y H:i:s') }}</p>
       <p>Aplikasi Sistem Sumber Elektronik Tata Aset (ASSETA) Tirta Perwira</p>
    </div>
</body>
</html>
