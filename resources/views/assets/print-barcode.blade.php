<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Stiker Aset - {{ $asset->assets_number }}</title>
    <style>
        @page {
            size: 100mm 40mm;
            margin: 0;
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
            padding: 20px 10px;
        }

        #print-area {
            display: block;
        }

        .sticker {
            width: 100mm;
            height: 38mm;
            max-height: 38mm;
            background: white;
            display: flex;
            overflow: hidden;
            margin: 0 auto 30px auto;
            border: 1px solid #ddd;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        /* KOLOM LOGO */
        .logo-column {
            width: 22mm;
            min-width: 22mm;
            height: 38mm;
            max-height: 38mm;
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
            height: 38mm;
            max-height: 38mm;
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

        /* QR CODE — INI YANG PALING PENTING */
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

        /* Support untuk QR code dalam format div/table dari DNS2D */
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

        /* PRINT STYLES */
        @media print {
            html, body {
                width: 100mm;
                height: 38mm;
                max-height: 38mm;
                margin: 0;
                padding: 0;
                background: white !important;
                overflow: hidden;
            }

            body {
                padding: 0;
            }

            #print-area {
                width: 100mm;
                height: 38mm;
                max-height: 38mm;
                overflow: hidden;
            }

            .sticker {
                width: 100mm;
                height: 38mm;
                max-height: 38mm;
                border: none;
                box-shadow: none;
                margin: 0;
                page-break-after: avoid;
                page-break-inside: avoid;
                overflow: hidden;
            }

            .no-print {
                display: none !important;
            }

            /* Force background colors to print */
            .logo-column {
                background: #1e40af !important;
                background-color: #1e40af !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
                height: 38mm;
                max-height: 38mm;
            }

            .content-column {
                height: 38mm;
                max-height: 38mm;
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

            /* QR Code harus tetap visible saat print */
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
        <h2>Preview Stiker Aset — {{ $asset->assets_number }}</h2>
        <button class="btn-print" onclick="window.print()">Print Stiker (Ctrl + P)</button>
        <p style="margin-top:10px; color:#666;">Ukuran: 100 × 40 mm</p>
    </div>

    <div id="print-area">
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
    </div>

    <script>
        function printBarcodes() {
            const copies = parseInt(document.getElementById('copies')?.value) || 1;
            const area = document.getElementById('print-area');
            const sticker = area.innerHTML;

            if (copies > 1) {
                area.innerHTML = sticker.repeat(copies);
            }
            window.print();
            if (copies > 1) {
                area.innerHTML = sticker;
            }
        }
    </script>
</body>

</html>
