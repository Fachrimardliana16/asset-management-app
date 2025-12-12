<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Stiker Aset Massal - {{ $assets->count() }} Aset</title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        html, body {
            height: auto;
            overflow: visible;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }

        #print-area {
            display: grid;
            grid-template-columns: repeat(2, 100mm);
            gap: 2mm;
            justify-content: center;
            max-width: 210mm;
            margin: 0 auto;
        }

        .sticker {
            width: 100mm;
            height: 40mm;
            background: white;
            display: flex;
            overflow: hidden;
            border: none;
            page-break-inside: avoid;
            position: relative;
        }

        /* Garis putus-putus vertikal di tengah antar kolom */
        .sticker:nth-child(odd)::after {
            content: '';
            position: absolute;
            right: -1mm;
            top: 0;
            bottom: 0;
            width: 1px;
            border-right: 1px dashed #999;
        }

        /* Garis putus-putus horizontal di bawah setiap baris kecuali baris terakhir */
        .sticker:nth-child(2n)::after {
            content: '';
            position: absolute;
            left: -101mm;
            bottom: -1mm;
            width: 202mm;
            height: 1px;
            border-bottom: 1px dashed #999;
        }

        /* Hilangkan garis bawah untuk 2 label terakhir di halaman */
        .sticker:nth-child(14n)::after,
        .sticker:nth-child(14n-1)::after {
            border-bottom: none;
        }

        /* KOLOM LOGO */
        .logo-column {
            width: 22mm;
            min-width: 22mm;
            height: 40mm;
            max-height: 40mm;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
            background-color: #1e40af !important;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5mm;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            overflow: hidden;
            flex-shrink: 0;
        }

        .logo-container {
            width: 13mm;
            height: 13mm;
            background: white !important;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5mm;
            overflow: hidden;
            flex-shrink: 0;
        }

        .logo-container img {
            width: 10mm;
            height: auto;
        }

        .company-text {
            font-size: 4.5pt;
            font-weight: bold;
            line-height: 1.15;
            text-transform: uppercase;
            text-align: center;
            color: white !important;
        }

        /* KONTEN UTAMA */
        .content-column {
            flex: 1;
            padding: 1.5mm 2mm;
            display: flex;
            flex-direction: column;
            height: 40mm;
            max-height: 40mm;
            overflow: hidden;
        }

        .header-row {
            border-bottom: 0.5px solid #e5e7eb;
            padding-bottom: 1mm;
            margin-bottom: 1mm;
            flex-shrink: 0;
        }

        .company-name {
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e40af !important;
        }

        .company-subtitle {
            font-size: 5pt;
            color: #666;
        }

        .main-content {
            display: flex;
            flex: 1;
            gap: 2mm;
            align-items: flex-start;
            overflow: hidden;
        }

        .info-section {
            flex: 1;
            overflow: visible;
            min-width: 0;
        }

        .asset-number {
            font-size: 10pt;
            font-weight: bold;
            color: #1e3a8a !important;
            margin-bottom: 0.8mm;
            letter-spacing: 0.3px;
        }

        .asset-name {
            font-size: 7pt;
            color: #222;
            line-height: 1.25;
            margin-bottom: 1.2mm;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .asset-meta {
            display: flex;
            gap: 1.5mm;
            flex-wrap: wrap;
            align-items: center;
        }

        .asset-category,
        .asset-brand {
            font-size: 6pt;
            padding: 0.8mm 2.5mm;
            border-radius: 2mm;
            font-weight: 500;
            white-space: nowrap;
            line-height: 1.2;
        }

        .asset-category {
            background: #dbeafe !important;
            background-color: #dbeafe !important;
            color: #1e40af !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .asset-brand {
            background: #f3f4f6 !important;
            background-color: #f3f4f6 !important;
            color: #444;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .asset-year {
            font-size: 6pt;
            padding: 0.8mm 2.5mm;
            border-radius: 2mm;
            font-weight: 500;
            white-space: nowrap;
            line-height: 1.2;
            background: #fef3c7 !important;
            background-color: #fef3c7 !important;
            color: #92400e !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* QR CODE */
        .qr-section {
            flex-shrink: 0;
            width: 22mm;
            min-width: 22mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            border-left: 0.5px solid #e5e7eb;
            padding: 3mm 1mm 1mm 1mm;
            overflow: visible;
        }

        .qr-code {
            width: 18mm;
            height: 18mm;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: visible;
            margin: 0;
            padding: 0;
        }

        .qr-code div,
        .qr-code table {
            display: block !important;
            visibility: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 18mm !important;
            height: 18mm !important;
        }

        .qr-code svg {
            width: 18mm !important;
            height: 18mm !important;
            max-width: 18mm !important;
            max-height: 18mm !important;
            display: block !important;
            visibility: visible !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .qr-label {
            font-size: 4pt;
            color: #555;
            margin-top: 2mm;
            text-align: center;
            line-height: 1.1;
            flex-shrink: 0;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            padding: 14px 36px;
            font-size: 18px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #1e3a8a;
        }

        /* PRINT STYLES */
        @media print {
            html, body {
                width: 210mm;
                margin: 0;
                padding: 0;
                background: white !important;
            }

            body {
                padding: 5mm;
            }

            #print-area {
                display: grid;
                grid-template-columns: repeat(2, 100mm);
                gap: 2mm;
                justify-content: center;
            }

            .sticker {
                width: 100mm;
                height: 40mm;
                border: none;
                margin: 0;
                page-break-inside: avoid;
                overflow: hidden;
            }

            /* Garis putus-putus vertikal di tengah antar kolom saat print */
            .sticker:nth-child(odd)::after {
                content: '';
                position: absolute;
                right: -1mm;
                top: 0;
                bottom: 0;
                width: 1px;
                border-right: 1px dashed #999;
            }

            /* Garis putus-putus horizontal di bawah setiap baris */
            .sticker:nth-child(2n)::after {
                content: '';
                position: absolute;
                left: -101mm;
                bottom: -1mm;
                width: 202mm;
                height: 1px;
                border-bottom: 1px dashed #999;
            }

            /* Hilangkan garis bawah untuk 2 label terakhir di halaman */
            .sticker:nth-child(14n)::after,
            .sticker:nth-child(14n-1)::after {
                border-bottom: none;
            }

            /* Setiap 14 label (2x7) pindah halaman */
            .sticker:nth-child(14n) {
                page-break-after: always;
            }

            .no-print {
                display: none !important;
            }

            .logo-column {
                background: #1e40af !important;
                background-color: #1e40af !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
                height: 40mm;
                max-height: 40mm;
            }

            .content-column {
                height: 40mm;
                max-height: 40mm;
            }

            .asset-category {
                background: #dbeafe !important;
                background-color: #dbeafe !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .asset-brand {
                background: #f3f4f6 !important;
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .asset-year {
                background: #fef3c7 !important;
                background-color: #fef3c7 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .qr-code,
            .qr-code * {
                display: block !important;
                visibility: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .qr-code div {
                background-color: inherit !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <h2>Preview Stiker Aset Massal — {{ $assets->count() }} Aset</h2>
        <button class="btn-print" onclick="window.print()">Print Semua Stiker (Ctrl + P)</button>
        <p style="margin-top:10px; color:#666;">
            Ukuran Label: 100 × 40 mm | Kertas: A4<br>
            Total: {{ $assets->count() }} label | Halaman: {{ ceil($assets->count() / 14) }} lembar<br>
            Layout: 2 kolom × 7 baris per halaman (14 label/halaman)
        </p>
    </div>

    <div id="print-area">
        @foreach($assets as $asset)
        <div class="sticker">
            <!-- LOGO -->
            <div class="logo-column">
                <div class="logo-container">
                    @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Logo">
                    @else
                    <span style="font-size:24pt;">Logo</span>
                    @endif
                </div>
                <div class="company-text">
                    PERUMDA<br>AIR MINUM<br>TIRTA PERWIRA
                </div>
            </div>

            <!-- KONTEN -->
            <div class="content-column">
                <div class="header-row">
                    <div class="company-name">Aset Perumda Air Minum Tirta Perwira</div>
                    <div class="company-subtitle">Kabupaten Purbalingga</div>
                </div>

                <div class="main-content">
                    <div class="info-section">
                        <div class="asset-number">{{ $asset->assets_number }}</div>
                        <div class="asset-name">{{ Str::limit($asset->name, 50) }}</div>
                        <div class="asset-meta">
                            <span class="asset-category">{{ $asset->categoryAsset->name ?? 'Umum' }}</span>
                            @if($asset->brand)
                            <span class="asset-brand">{{ $asset->brand }}</span>
                            @endif
                            @if($asset->purchase_date)
                            <span class="asset-year">Tahun {{ $asset->purchase_date->format('Y') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- QR CODE DINAMIS DARI DATABASE -->
                    <div class="qr-section">
                        <div class="qr-code">
                            {!! DNS2D::getBarcodeSVG(route('asset.scan', $asset->id), 'QRCODE', 2, 2) !!}
                        </div>
                        <div class="qr-label">Scan untuk<br>Monitoring</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</body>

</html>