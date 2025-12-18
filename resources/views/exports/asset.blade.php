<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Data Aset</title>

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
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        /* ===== FILTER ===== */
        .filter-info {
            text-align: center;
            font-size: 10px;
            margin-bottom: 12px;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .summary-title {
        margin-top: 25px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        }

        .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
        }

        .summary-table th,
        .summary-table td {
        border: 1px solid #000;
        padding: 5px 6px;
        font-size: 10px;
        }

        .summary-table th {
        background-color: #f2f2f2;
        text-align: center;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 18px;
            text-align: right;
            font-size: 9px;
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
                <td width="70"></td>
            </tr>
        </table>
        <div class="header-line"></div>
    </div>

    <!-- TITLE -->
    <div class="report-title">
        Laporan Data Aset
    </div>

    <!-- FILTER INFO -->
    <div class="filter-info">
        @if($startDate && $endDate)
        <strong>Periode:</strong>
        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        –
        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @endif

        @if($condition)
        | <strong>Kondisi:</strong> {{ $condition }}
        @endif

        @if($status)
        | <strong>Status:</strong> {{ $status }}
        @endif

        @if(!$startDate && !$endDate && !$condition && !$status)
        <em>Menampilkan seluruh data aset</em>
        @endif
    </div>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th width="25">No</th>
                <th>Nomor Aset</th>
                <th>Nama Aset</th>
                <th>Merk</th>
                <th>Tgl Perolehan</th>
                <th>Kondisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->assets_number ?? '-' }}</td>
                <td class="text-center">{{ $item->name ?? '-' }}</td>
                <td class="text-center">{{ $item->brand ?? '-' }}</td>
                <td class="text-center">
                    {{ $item->purchase_date
                    ? \Carbon\Carbon::parse($item->purchase_date)->format('d/m/Y')
                    : '-' }}
                </td>
                <td class="text-center">{{ $item->conditionAsset->name ?? '-' }}</td>
                <td class="text-center">{{ $item->assetsStatus->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">
                    Tidak ada data
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-title">Ringkasan Berdasarkan Kategori</div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Kategori Aset</th>
                <th width="120">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summaryCategory as $item)
            <tr>
                <td>{{ $item->category_name }}</td>
                <td class="text-center">{{ $item->total }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-title">Ringkasan Berdasarkan Kondisi Aset</div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Kondisi Aset</th>
                <th width="120">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summaryCondition as $item)
            <tr>
                <td>{{ $item->condition_name }}</td>
                <td class="text-center">{{ $item->total }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-title">Ringkasan Berdasarkan Status Aset</div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Status Aset</th>
                <th width="120">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summaryStatus as $item)
            <tr>
                <td>{{ ucfirst($item->status_name) }}</td>
                <td class="text-center">{{ $item->total }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center">Tidak ada data</td>
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
