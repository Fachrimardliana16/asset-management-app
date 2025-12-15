<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Scanner Section --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-qr-code class="w-6 h-6 text-primary-500" />
                    <span>Scan QR Code Aset</span>
                </div>
            </x-slot>

            <x-slot name="description">
                Gunakan kamera untuk scan QR Code atau masukkan nomor aset secara manual
            </x-slot>

            <div class="space-y-4">
                {{-- Camera Scanner --}}
                <div x-data="barcodeScanner()" x-init="init()" class="space-y-4">
                    <div class="flex flex-col gap-4 md:flex-row">
                        {{-- Camera Preview --}}
                        <div class="flex-1">
                            <div class="relative overflow-hidden bg-gray-900 rounded-lg aspect-video md:aspect-auto"
                                style="min-height: 350px;">
                                {{-- QR Reader Container - always visible but content controlled by library --}}
                                <div id="qr-reader" class="w-full" :class="cameraActive ? '' : 'hidden'"></div>

                                {{-- Placeholder when camera not active --}}
                                <div x-show="!cameraActive"
                                    class="flex flex-col items-center justify-center py-16 text-gray-400">
                                    <svg class="w-20 h-20 mb-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    <p class="text-lg text-center">Kamera tidak aktif</p>
                                    <p class="mt-1 text-sm text-gray-500">Klik tombol di bawah untuk scan QR Code</p>
                                </div>
                            </div>

                            <div class="flex gap-2 mt-4">
                                <button @click="cameraActive ? stopCamera() : startCamera()" type="button"
                                    class="flex items-center justify-center flex-1 gap-2 px-4 py-3 font-medium transition rounded-lg"
                                    :class="cameraActive ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-green-500 text-white hover:bg-green-600'">
                                    <span x-show="cameraActive" class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                        </svg>
                                        Stop Kamera
                                    </span>
                                    <span x-show="!cameraActive" class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Mulai Scan QR Code
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- Manual Input --}}
                        <div class="flex-1 space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Input Manual Nomor Aset
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" wire:model="barcodeInput" placeholder="Masukkan nomor aset..."
                                        class="flex-1 border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                        @keydown.enter="$wire.searchAsset()">
                                    <button wire:click="searchAsset" type="button"
                                        class="flex items-center gap-2 px-6 py-2 text-white transition rounded-lg bg-primary-500 hover:bg-primary-600">
                                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                        Cari
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <h4 class="mb-2 font-semibold text-blue-700 dark:text-blue-300">📱 Petunjuk Scan QR
                                    Code:</h4>
                                <ol class="space-y-1 text-sm text-blue-600 list-decimal list-inside dark:text-blue-400">
                                    <li>Klik <strong>"Mulai Scan QR Code"</strong></li>
                                    <li>Izinkan akses kamera jika diminta</li>
                                    <li>Arahkan kamera ke QR Code pada stiker</li>
                                    <li>Posisikan QR Code di dalam kotak</li>
                                    <li>Atau input nomor aset manual</li>
                                </ol>
                            </div>

                            {{-- Last scanned info --}}
                            <div x-show="lastScanned" x-cloak class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20">
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    <span class="font-semibold">✅ Terakhir di-scan:</span>
                                    <span x-text="lastScanned"></span>
                                </p>
                            </div>

                            {{-- Error message --}}
                            <div x-show="errorMessage" x-cloak class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20">
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    <span class="font-semibold">❌ Error:</span>
                                    <span x-text="errorMessage"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Form Section --}}
        <form wire:submit="saveMonitoring">
            {{ $this->form }}

            @if($showAssetInfo)
            <div class="flex gap-4 mt-6">
                <x-filament::button type="submit" color="success" size="lg">
                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                    Simpan Monitoring
                </x-filament::button>

                <x-filament::button type="button" color="gray" size="lg" wire:click="resetForm">
                    <x-heroicon-o-arrow-path class="w-5 h-5 mr-2" />
                    Reset
                </x-filament::button>
            </div>
            @endif
        </form>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        #qr-reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
        }

        #qr-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        #qr-reader__scan_region {
            background: transparent !important;
        }

        #qr-reader__scan_region video {
            width: 100% !important;
            height: auto !important;
        }

        #qr-reader__dashboard {
            padding: 10px !important;
        }

        #qr-reader__dashboard_section_csr button {
            background-color: #3b82f6 !important;
            color: white !important;
            border-radius: 0.5rem !important;
            padding: 8px 16px !important;
        }
    </style>

    {{-- QR Code Scanner Script --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function barcodeScanner() {
            return {
                cameraActive: false,
                scanning: false,
                lastScanned: null,
                errorMessage: null,
                html5QrCode: null,

                init() {
                    // Cleanup on page leave
                    window.addEventListener('beforeunload', () => {
                        this.stopCamera();
                    });
                },

                async startCamera() {
                    this.errorMessage = null;

                    try {
                        // Clear previous instance if exists
                        if (this.html5QrCode) {
                            try {
                                await this.html5QrCode.stop();
                            } catch (e) {}
                        }

                        // Show the qr-reader div first
                        this.cameraActive = true;

                        // Small delay to ensure DOM is updated
                        await new Promise(resolve => setTimeout(resolve, 100));

                        // Initialize scanner
                        this.html5QrCode = new Html5Qrcode("qr-reader");

                        // qrbox dinamis - 75% dari sisi terkecil viewfinder
                        const qrboxFunction = (viewfinderWidth, viewfinderHeight) => {
                            const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                            const qrboxSize = Math.floor(minEdge * 0.75); // Ubah 0.75 sesuai kebutuhan (0.7-0.8 bagus)
                            return { width: qrboxSize, height: qrboxSize };
                        };

                        const config = {
                            fps: 10,
                            qrbox: qrboxFunction,
                            showTorchButtonIfSupported: true,
                            showZoomSliderIfSupported: true,
                        };

                        const self = this;

                        await this.html5QrCode.start(
                            { facingMode: "environment" },
                            config,
                            (decodedText, decodedResult) => {
                                // On successful scan
                                console.log('QR Code detected:', decodedText);
                                self.lastScanned = decodedText;

                                // Extract asset number from various formats
                                let assetNumber = decodedText;

                                // Check if it's a URL containing asset scan route
                                if (decodedText.includes('/asset/scan/')) {
                                    // Extract the ID from the URL and fetch asset number via API or redirect
                                    // For now, redirect to the scanned URL which will redirect back with asset number
                                    self.stopCamera();
                                    window.location.href = decodedText;
                                    return;
                                }

                                // Check for URL with asset parameter
                                if (decodedText.includes('asset=')) {
                                    try {
                                        const url = new URL(decodedText);
                                        assetNumber = url.searchParams.get('asset') || decodedText;
                                    } catch (e) {
                                        // Try to extract with regex if URL parsing fails
                                        const match = decodedText.match(/asset=([^&]+)/);
                                        if (match) {
                                            assetNumber = match[1];
                                        }
                                    }
                                }

                                // Send to Livewire
                                self.$wire.set('barcodeInput', assetNumber);
                                self.$wire.searchAsset();

                                // Stop scanning after successful read
                                self.stopCamera();
                            },
                            (errorMessage) => {
                                // Ignore - just means no QR detected yet
                            }
                        );

                        // Workaround: Hapus inline width yang di-set library agar responsif
                        const videoElement = document.querySelector('#qr-reader video');
                        if (videoElement) {
                            videoElement.style.width = '100% !important';
                            videoElement.style.height = '100% !important';
                            videoElement.style.removeProperty('width');
                            videoElement.style.removeProperty('height');
                        }

                        this.scanning = true;

                    } catch (err) {
                        console.error('Error starting camera:', err);
                        this.cameraActive = false;
                        this.errorMessage = 'Gagal mengakses kamera: ' + (err.message || err);

                        if (err.name === 'NotAllowedError') {
                            this.errorMessage = 'Izin kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.';
                        } else if (err.name === 'NotFoundError') {
                            this.errorMessage = 'Kamera tidak ditemukan. Pastikan perangkat memiliki kamera.';
                        } else if (err.name === 'NotReadableError') {
                            this.errorMessage = 'Kamera sedang digunakan aplikasi lain.';
                        }
                    }
                },

                async stopCamera() {
                    try {
                        if (this.html5QrCode) {
                            await this.html5QrCode.stop();
                            this.html5QrCode.clear();
                            this.html5QrCode = null;
                        }
                    } catch (err) {
                        console.error('Error stopping camera:', err);
                    }
                    this.cameraActive = false;
                    this.scanning = false;
                }
            }
        }
    </script>
</x-filament-panels::page>
