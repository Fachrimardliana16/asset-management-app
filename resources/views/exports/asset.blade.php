<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Aset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
        }
        .header-content {
            display: inline-block;
        }
        .logo {
            width: 70px;
            height: auto;
            float: left;
            margin-right: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .company-subtitle {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 10px;
            text-transform: uppercase;
        }
        .filter-info {
            text-align: center;
            margin-bottom: 15px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
        @endif
        <div class="header-content">
            <p class="company-name">Perusahaan Umum Daerah Air Minum</p>
            <p class="company-subtitle">Tirta Perwira Kabupaten Purbalingga</p>
        </div>
    </div>

    <div class="report-title">
        Laporan Data Aset
    </div>

    <div class="filter-info">
        @if($startDate && $endDate)
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @endif
        @if($condition)
            | Kondisi: {{ $condition }}
        @endif
        @if($status)
            | Status: {{ $status }}
        @endif
        @if(!$startDate && !$endDate && !$condition && !$status)
            Semua Data
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Nomor Aset</th>
                <th>Nama Aset</th>
                <th>Kategori</th>
                <th>Merk</th>
                <th>Tgl Perolehan</th>
                <th>Harga</th>
                <th>Nilai Buku</th>
                <th>Kondisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->assets_number ?? '-' }}</td>
                    <td>{{ $item->name ?? '-' }}</td>
                    <td>{{ $item->categoryAsset->name ?? '-' }}</td>
                    <td>{{ $item->brand ?? '-' }}</td>
                    <td>{{ $item->purchase_date ? \Carbon\Carbon::parse($item->purchase_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-right">{{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->book_value ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->conditionAsset->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->assetsStatus->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
