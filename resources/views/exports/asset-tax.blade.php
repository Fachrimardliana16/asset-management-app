<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Pajak Aset</title>

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

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-cancelled {
            background-color: #e2e3e5;
            color: #383d41;
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
            </tr>
        </table>
        <div class="header-line"></div>
    </div>

    <!-- TITLE -->
    <p class="report-title">Laporan Pajak Aset</p>

    <!-- FILTER INFO -->
    <div class="filter-info">
        @if($filters['tax_year'] !== 'Semua')
        <strong>Tahun Pajak:</strong> {{ $filters['tax_year'] }} |
        @endif
        @if($filters['tax_type'] !== 'Semua')
        <strong>Jenis Pajak:</strong> {{ $filters['tax_type'] }} |
        @endif
        @if($filters['payment_status'] !== 'Semua')
        <strong>Status:</strong> {{ ucfirst($filters['payment_status']) }} |
        @endif
        @if($filters['due_date_start'] && $filters['due_date_end'])
        <strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($filters['due_date_start'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['due_date_end'])->format('d/m/Y') }} |
        @endif
        <strong>Dicetak:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    <!-- TABLE DATA -->
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="12%">Kode Aset</th>
                <th width="18%">Nama Aset</th>
                <th width="12%">Jenis Pajak</th>
                <th width="6%">Tahun</th>
                <th width="10%">Nilai Pajak</th>
                <th width="8%">Denda</th>
                <th width="9%">Jatuh Tempo</th>
                <th width="9%">Tgl Bayar</th>
                <th width="8%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTax = 0;
                $totalPenalty = 0;
                $totalPaid = 0;
                $totalUnpaid = 0;
            @endphp

            @forelse($data as $index => $item)
            @php
                $totalTax += $item->tax_amount;
                $totalPenalty += $item->penalty_amount;
                if($item->payment_status === 'paid') {
                    $totalPaid += ($item->tax_amount + $item->penalty_amount);
                } else {
                    $totalUnpaid += ($item->tax_amount + $item->penalty_amount);
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->asset->assets_number ?? '-' }}</td>
                <td>{{ $item->asset->name ?? '-' }}</td>
                <td>{{ $item->taxType->name ?? '-' }}</td>
                <td class="text-center">{{ $item->tax_year }}</td>
                <td class="text-right">Rp {{ number_format($item->tax_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->penalty_amount, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $item->payment_date ? \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y') : '-' }}</td>
                <td class="text-center">
                    @if($item->payment_status === 'paid')
                        <span class="status-badge status-paid">LUNAS</span>
                    @elseif($item->payment_status === 'pending')
                        <span class="status-badge status-pending">PENDING</span>
                    @elseif($item->payment_status === 'overdue')
                        <span class="status-badge status-overdue">TERLAMBAT</span>
                    @else
                        <span class="status-badge status-cancelled">BATAL</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse

            @if($data->count() > 0)
            <tr>
                <td colspan="5" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalTax, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalPenalty, 0, ',', '.') }}</strong></td>
                <td colspan="3" class="text-right"><strong>Rp {{ number_format($totalTax + $totalPenalty, 0, ',', '.') }}</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- SUMMARY -->
    @if($data->count() > 0)
    <div class="summary-title">Ringkasan</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total Pajak</th>
                <th>Total Denda</th>
                <th>Total Keseluruhan</th>
                <th>Sudah Dibayar</th>
                <th>Belum Dibayar</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right">Rp {{ number_format($totalTax, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalPenalty, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($totalTax + $totalPenalty, 0, ',', '.') }}</strong></td>
                <td class="text-right" style="background-color: #d4edda;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                <td class="text-right" style="background-color: #f8d7da;">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WIB</p>
        <p>Perusahaan Umum Daerah Air Minum Tirta Perwira Kabupaten Purbalingga</p>
    </div>

</body>

</html>
