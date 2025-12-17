<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Pemeliharaan Aset</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        /* ===== HEADER ===== */
        .header {
            margin-bottom: 15px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            border: none;
        }

        .logo {
            width: 65px;
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
            margin: 3px 0 0 0;
        }

        .company-address {
            font-size: 10px;
            margin: 2px 0 0 0;
            line-height: 1.3;
        }

        .header-line {
            border-top: 3px double #000;
            margin-top: 10px;
        }

        /* ===== TITLE ===== */
        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0 5px;
        }

        /* ===== FILTER INFO ===== */
        .filter-info {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 7px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        td {
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <table>
            <tr>
                <td width="80" align="center">
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
                <td width="80"></td>
            </tr>
        </table>
        <div class="header-line"></div>
    </div>

    <!-- TITLE -->
    <div class="report-title">
        Laporan Pemeliharaan Aset
    </div>

    <!-- FILTER INFO -->
    <div class="filter-info">
        @if($startDate && $endDate)
        <strong>Periode:</strong>
        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        –
        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @endif

        @if($serviceType)
        | <strong>Jenis Servis:</strong> {{ $serviceType }}
        @endif

        @if(!$startDate && !$endDate && !$serviceType)
        <em>Menampilkan seluruh data</em>
        @endif
    </div>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
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
                <td class="text-center">
                    {{ $item->maintenance_date
                    ? \Carbon\Carbon::parse($item->maintenance_date)->format('d/m/Y')
                    : '-' }}
                </td>
                <td>{{ $item->AssetMaintenance->name ?? '-' }}</td>
                <td class="text-center">{{ $item->service_type ?? '-' }}</td>
                <td>{{ $item->location_service ?? '-' }}</td>
                <td class="text-right">
                    {{ number_format($item->service_cost ?? 0, 0, ',', '.') }}
                </td>
                <td>{{ $item->desc ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">
                    Tidak ada data
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>

</html>
