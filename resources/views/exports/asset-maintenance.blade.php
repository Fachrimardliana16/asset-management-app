<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemeliharaan Aset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
            padding: 6px 8px;
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
        Laporan Pemeliharaan Aset
    </div>

    <div class="filter-info">
        @if($startDate && $endDate)
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @endif
        @if($serviceType)
            | Jenis Servis: {{ $serviceType }}
        @endif
        @if(!$startDate && !$endDate && !$serviceType)
            Semua Data
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Tanggal</th>
                <th>Nama Aset</th>
                <th>Jenis Servis</th>
                <th>Lokasi Servis</th>
                <th>Biaya</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->maintenance_date ? \Carbon\Carbon::parse($item->maintenance_date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->AssetMaintenance->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->service_type ?? '-' }}</td>
                    <td>{{ $item->location_service ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->service_cost ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $item->desc ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
