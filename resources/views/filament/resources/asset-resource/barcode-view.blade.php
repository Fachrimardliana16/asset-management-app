<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-content p-6">
        <div class="flex flex-col lg:flex-row gap-6 items-center lg:items-start">
            {{-- QR Code Section --}}
            <div class="flex flex-col items-center">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    {!! DNS2D::getBarcodeHTML($getRecord()->assets_number, 'QRCODE', 5, 5) !!}
                </div>
                <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">
                    {{ $getRecord()->assets_number }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Scan QR untuk identifikasi
                </p>
            </div>
            
            {{-- Asset Info Section --}}
            <div class="flex-1 text-center lg:text-left">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    {{ $getRecord()->name }}
                </h3>
                
                <div class="flex flex-wrap gap-2 justify-center lg:justify-start mb-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-500/20 dark:text-primary-400">
                        {{ $getRecord()->categoryAsset?->name ?? '-' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ $getRecord()->brand ?? '-' }}
                    </span>
                </div>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Gunakan tombol di bawah untuk mencetak stiker QR Code yang dapat ditempelkan pada aset fisik.
                </p>
                
                <button 
                    type="button"
                    onclick="window.open('{{ route('asset.print-barcode', $getRecord()->id) }}', '_blank')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-success-600 rounded-lg shadow-sm hover:bg-success-500 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Stiker QR Code
                </button>
            </div>
        </div>
    </div>
</div>
