<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Faktur Pembelian - {{ $request->document_number }}</title>
    <style>
        /* 1. Mengurangi Ukuran Font Dasar */
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            /* Diperkecil dari 12px */
            margin: 0;
            padding: 10px;
            /* Padding dikurangi */
            color: #333;
        }

        /* 2. Mengurangi Padding & Margin */
        .header {
            text-align: center;
            margin-bottom: 15px;
            /* Dikurangi */
            border-bottom: 2px solid #000;
            /* Border diperkecil */
            padding-bottom: 10px;
            /* Dikurangi */
        }

        .header-content {
            display: inline-block;
        }

        /* 3. Memadatkan Header */
        .logo {
            width: 50px;
            /* Diperkecil dari 80px */
            height: auto;
            float: left;
            margin-right: 10px;
            /* Dikurangi */
        }

        .company-name {
            font-size: 14px;
            /* Dikurangi */
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            color: #1a5490;
        }

        .company-subtitle {
            font-size: 16px;
            /* Dikurangi */
            font-weight: bold;
            margin: 3px 0;
            /* Dikurangi */
            text-transform: uppercase;
            color: #1a5490;
        }

        .company-address {
            font-size: 9px;
            /* Dikurangi */
            color: #666;
            margin-top: 3px;
            /* Dikurangi */
        }

        .invoice-title {
            font-size: 16px;
            /* Dikurangi */
            font-weight: bold;
            text-align: center;
            margin: 15px 0 10px;
            /* Dikurangi */
            text-transform: uppercase;
            color: #1a5490;
            border-bottom: 1px solid #1a5490;
            /* Diperkecil */
            padding-bottom: 5px;
            /* Dikurangi */
        }

        /* 4. Memadatkan Bagian Informasi */
        .invoice-info {
            margin-bottom: 10px;
            /* Dikurangi */
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            /* Dikurangi */
        }

        .info-label {
            display: table-cell;
            width: 130px;
            /* Diperkecil */
            font-weight: bold;
            color: #555;
            padding-right: 5px;
        }

        .info-value {
            display: table-cell;
        }

        .section-title {
            font-size: 12px;
            /* Dikurangi */
            font-weight: bold;
            margin-top: 15px;
            /* Dikurangi */
            margin-bottom: 5px;
            /* Dikurangi */
            color: #1a5490;
            border-bottom: 1px solid #e0e0e0;
            /* Diperkecil */
            padding-bottom: 3px;
            /* Dikurangi */
        }

        /* 5. Memadatkan Tabel */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            /* Dikurangi */
            margin-bottom: 10px;
            /* Dikurangi */
            font-size: 10px;
        }

        table.items-table th,
        table.items-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            /* Padding dikurangi */
            text-align: left;
        }

        table.items-table th {
            background-color: #1a5490;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        table.items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total-section {
            margin-top: 10px;
            /* Dikurangi */
            float: right;
            width: 300px;
            /* Diperkecil */
        }

        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            /* Dikurangi */
            padding: 5px 8px;
            /* Padding dikurangi */
        }

        .total-label {
            display: table-cell;
            font-weight: bold;
            width: 150px;
            /* Diperkecil */
            color: #555;
        }

        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }

        .grand-total {
            background-color: #1a5490;
            color: white;
            padding: 8px 12px;
            /* Padding dikurangi */
            font-size: 11px;
        }

        .grand-total .total-label,
        .grand-total .total-value {
            color: white;
        }

        /* 6. Memindahkan Bagian Tanda Tangan */
        .signature-section {
            margin-top: 30px;
            /* Dikurangi */
            clear: both;
        }

        .signature-box {
            display: inline-block;
            width: 40%;
            /* Ditingkatkan untuk menampung tanggal di kanan */
            text-align: center;
            vertical-align: top;
        }

        .signature-box.right {
            float: right;
        }

        .signature-box.left {
            float: left;
        }

        .signature-line {
            margin-top: 50px;
            /* Dikurangi */
            border-top: 1px solid #000;
            padding-top: 3px;
            /* Dikurangi */
            font-weight: bold;
        }

        .notes-section {
            margin-top: 15px;
            /* Dikurangi */
            padding: 10px;
            /* Dikurangi */
            background-color: #f9f9f9;
            border-left: 3px solid #1a5490;
            /* Diperkecil */
            clear: both;
            font-size: 10px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
            /* Dikurangi */
            color: #1a5490;
        }

        .footer {
            margin-top: 15px;
            /* Dikurangi */
            text-align: center;
            font-size: 8px;
            /* Diperkecil */
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            /* Dikurangi */
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            /* Padding dikurangi */
            border-radius: 3px;
            font-size: 9px;
            /* Diperkecil */
            font-weight: bold;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>

<body>
    <div class="clearfix header">
        @if(file_exists(public_path('images/logo.png')))
        <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
        @endif
        <div class="header-content">
            <p class="company-name">Perusahaan Umum Daerah Air Minum</p>
            <p class="company-subtitle">Tirta Perwira Kabupaten Purbalingga</p>
            <p class="company-address">Jl. Letnan Jenderal S Parman No.62, Kedung Menjangan, Bancar, Kec. Purbalingga, Kabupaten Purbalingga, Jawa Tengah 53316</p>
        </div>
    </div>

    <div class="invoice-title">
        Faktur / Bukti Pembelian Aset
    </div>

    <div class="invoice-info">
        <div class="info-row">
            <div class="info-label">No. Dokumen</div>
            <div class="info-value">: <strong>{{ $request->document_number }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Permintaan</div>
            <div class="info-value">: {{ $request->date->format('d F Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Pembelian</div>
            <div class="info-value">: <strong>{{ $purchase->purchase_date ?
                    \Carbon\Carbon::parse($purchase->purchase_date)->format('d F Y') : '-' }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value">: <span class="badge badge-success">Sudah Dibeli</span></div>
        </div>
    </div>

    <div class="section-title">Detail Barang</div>
    <div class="invoice-info">
        <div class="info-row">
            <div class="info-label">Nama Barang</div>
            <div class="info-value">: <strong>{{ $request->asset_name }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Kategori</div>
            <div class="info-value">: {{ $request->category?->name }} ({{ $request->category?->kode }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">Merk / Tipe</div>
            <div class="info-value">: {{ $purchase->brand }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Jumlah</div>
            <div class="info-value">: <strong>{{ $request->quantity }} unit</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Keperluan</div>
            <div class="info-value">: {{ $request->purpose }}</div>
        </div>
        @if($request->desc)
        <div class="info-row">
            <div class="info-label">Keterangan</div>
            <div class="info-value">: {{ $request->desc }}</div>
        </div>
        @endif
    </div>

    <div class="section-title">Informasi Pemohon & Lokasi</div>
    <div class="invoice-info">
        <div class="info-row">
            <div class="info-label">Pemohon</div>
            <div class="info-value">: {{ $request->employee?->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Lokasi</div>
            <div class="info-value">: {{ $request->location?->name }} ({{ $request->location?->kode }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">Sub Lokasi</div>
            <div class="info-value">: {{ $request->subLocation?->name ?? '-' }}</div>
        </div>
    </div>

    <div class="section-title">Daftar Nomor Aset yang Dibuat</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nomor Aset</th>
                <th style="width: 30%;">Nama Barang</th>
                <th style="width: 20%;">Kondisi</th>
                <th style="width: 20%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center"><strong>{{ $item->assets_number }}</strong></td>
                <td>{{ $item->asset_name }}</td>
                <td class="text-center">{{ $item->condition?->name ?? '-' }}</td>
                <td class="text-center">{{ $item->status?->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Rincian Biaya</div>
    <div class="total-section">
        <div class="total-row">
            <div class="total-label">Harga Satuan</div>
            <div class="total-value">Rp {{ number_format($purchase->price, 0, ',', '.') }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">Jumlah Unit</div>
            <div class="total-value">{{ $request->quantity }} unit</div>
        </div>
        <div class="total-row">
            <div class="total-label">Nilai Buku (per unit)</div>
            <div class="total-value">Rp {{ number_format($purchase->book_value, 0, ',', '.') }}</div>
        </div>
        <div class="total-row grand-total">
            <div class="total-label">TOTAL HARGA</div>
            <div class="total-value">Rp {{ number_format($total_price, 0, ',', '.') }}</div>
        </div>
    </div>

    <div style="clear: both;"></div>

    <div class="section-title" style="margin-top: 15px;">Informasi Tambahan</div>
    <div class="invoice-info">
        <div class="info-row">
            <div class="info-label">Sumber Dana</div>
            <div class="info-value">: {{ $purchase->funding_source }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Habis Nilai Buku</div>
            <div class="info-value">: {{ $purchase->book_value_expiry ?
                \Carbon\Carbon::parse($purchase->book_value_expiry)->format('d F Y') : '-' }}</div>
        </div>
        @if($purchase->purchase_notes)
        <div class="info-row">
            <div class="info-label">Catatan Pembelian</div>
            <div class="info-value">: {{ $purchase->purchase_notes }}</div>
        </div>
        @endif
    </div>

    @if($request->purchase_notes)
    <div class="notes-section">
        <div class="notes-title">Catatan:</div>
        <div>{{ $request->purchase_notes }}</div>
    </div>
    @endif

    <div class="clearfix signature-section">
        <div class="signature-box left">
            <div>Mengetahui,</div>
            <div><strong>Kepala Sub Bagian Kerumahtanggaan</strong></div>
            <div class="signature-line">
                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
            </div>
        </div>

        <div class="signature-box right">
            <div>Purbalingga, {{ $printed_at->format('d F Y') }}</div>
            <div><strong>Petugas Pembelian</strong></div>
            <div class="signature-line">
                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis pada {{ $printed_at->format('d F Y H:i:s') }}</p>
        <p>Aplikasi Sistem Sumber Elektronik Tata Aset (ASSETA) Tirta Perwira</p>
    </div>
</body>

</html>
