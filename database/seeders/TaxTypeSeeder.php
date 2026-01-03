<?php

namespace Database\Seeders;

use App\Models\MasterTaxType;
use App\Models\MasterAssetsCategory;
use Illuminate\Database\Seeder;

class TaxTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini berisi jenis-jenis pajak aset sesuai peraturan perpajakan di Indonesia
     */
    public function run(): void
    {
        // Ambil kategori aset berdasarkan kode
        $categories = [
            'tanah'         => MasterAssetsCategory::where('kode', '3101')->first(), // Tanah dan Penyempurnaan Tanah
            'instalasi_air' => MasterAssetsCategory::where('kode', '3102')->first(), // Instalasi Sumber Air
            'instalasi_pompa' => MasterAssetsCategory::where('kode', '3103')->first(), // Instalasi Pompa
            'ipa_ipam'      => MasterAssetsCategory::where('kode', '3104')->first(), // IPA/IPAM
            'transmisi'     => MasterAssetsCategory::where('kode', '3105')->first(), // Transmisi & Distribusi
            'bangunan'      => MasterAssetsCategory::where('kode', '3106')->first(), // Bangunan dan Gedung
            'peralatan'     => MasterAssetsCategory::where('kode', '3107')->first(), // Peralatan dan Perlengkapan
            'kendaraan'     => MasterAssetsCategory::where('kode', '3108')->first(), // Kendaraan / Alat Angkut
            'inventaris'    => MasterAssetsCategory::where('kode', '3109')->first(), // Inventaris / Perabot Kantor
        ];

        $taxTypes = [
            // ========================================
            // PAJAK KENDARAAN BERMOTOR (Kategori 3108)
            // ========================================
            [
                'name' => 'PKB (Pajak Kendaraan Bermotor)',
                'code' => 'PKB',
                'description' => 'Pajak tahunan untuk kepemilikan dan/atau penguasaan kendaraan bermotor. Dasar hukum: UU No. 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah.',
                'asset_category_id' => $categories['kendaraan']?->id,
                'period_type' => 'yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 2.0, // 2% per bulan keterlambatan
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'BBNKB (Bea Balik Nama Kendaraan Bermotor)',
                'code' => 'BBNKB',
                'description' => 'Pajak atas penyerahan hak milik kendaraan bermotor. Dibayar saat pembelian kendaraan baru atau bekas (balik nama). Dasar hukum: UU No. 28 Tahun 2009.',
                'asset_category_id' => $categories['kendaraan']?->id,
                'period_type' => 'custom',
                'period_months' => 60, // 5 tahun (perpanjangan STNK)
                'has_penalty' => true,
                'penalty_percentage' => 1.5, // 1.5% per bulan
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'SWDKLLJ (Sumbangan Wajib Dana Kecelakaan Lalu Lintas Jalan)',
                'code' => 'SWDKLLJ',
                'description' => 'Iuran wajib sebagai dana pertanggungan wajib kecelakaan lalu lintas jalan. Dibayar bersamaan dengan PKB. Dasar hukum: UU No. 34 Tahun 1964.',
                'asset_category_id' => $categories['kendaraan']?->id,
                'period_type' => 'yearly',
                'period_months' => null,
                'has_penalty' => false, // Biasanya bundel dengan PKB
                'penalty_percentage' => null,
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 30,
                'is_active' => true,
            ],

            // ========================================
            // PAJAK TANAH DAN BANGUNAN (Kategori 3101 & 3106)
            // ========================================
            [
                'name' => 'PBB-P2 (Pajak Bumi dan Bangunan Perdesaan dan Perkotaan)',
                'code' => 'PBB',
                'description' => 'Pajak atas bumi dan/atau bangunan yang dimiliki, dikuasai, dan/atau dimanfaatkan. Dasar hukum: UU No. 28 Tahun 2009. Tarif maksimal 0,3% dari NJOP.',
                'asset_category_id' => $categories['tanah']?->id,
                'period_type' => 'yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 2.0, // 2% per bulan, maksimal 24 bulan
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'PBB Bangunan (Pajak Bumi dan Bangunan untuk Gedung)',
                'code' => 'PBB-BANGUNAN',
                'description' => 'Pajak atas bangunan dan gedung yang dimiliki. Termasuk gedung kantor, pabrik, gudang, dll. Tarif maksimal 0,3% dari NJOP bangunan.',
                'asset_category_id' => $categories['bangunan']?->id,
                'period_type' => 'yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 2.0,
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'BPHTB (Bea Perolehan Hak atas Tanah dan Bangunan)',
                'code' => 'BPHTB',
                'description' => 'Pajak atas perolehan hak atas tanah/bangunan. Dibayar saat jual-beli, hibah, waris, atau perolehan hak lainnya. Tarif 5% dari NPOP. Dasar hukum: UU No. 28 Tahun 2009.',
                'asset_category_id' => $categories['tanah']?->id,
                'period_type' => 'custom',
                'period_months' => 1, // Dibayar sekali saat transaksi
                'has_penalty' => true,
                'penalty_percentage' => 2.0, // 2% per bulan
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 30,
                'is_active' => true,
            ],

            // ========================================
            // RETRIBUSI DAN PERIZINAN BANGUNAN
            // ========================================
            [
                'name' => 'PBG (Persetujuan Bangunan Gedung)',
                'code' => 'PBG',
                'description' => 'Retribusi perizinan mendirikan bangunan (dulu IMB). Dasar hukum: UU No. 28 Tahun 2002 tentang Bangunan Gedung, Perpres No. 18 Tahun 2020.',
                'asset_category_id' => $categories['bangunan']?->id,
                'period_type' => '5yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 100000.00, // Denda tetap
                'penalty_type' => 'fixed',
                'penalty_period' => 'monthly',
                'reminder_days' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'SLF (Sertifikat Laik Fungsi Bangunan)',
                'code' => 'SLF',
                'description' => 'Sertifikat yang menyatakan bangunan gedung layak fungsi. Wajib untuk bangunan fungsi khusus/umum. Perpanjangan setiap 5 tahun untuk bangunan permanen.',
                'asset_category_id' => $categories['bangunan']?->id,
                'period_type' => '5yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 50000.00,
                'penalty_type' => 'fixed',
                'penalty_period' => 'monthly',
                'reminder_days' => 90,
                'is_active' => true,
            ],

            // ========================================
            // RETRIBUSI IZIN LINGKUNGAN DAN AIR
            // ========================================
            [
                'name' => 'Retribusi Izin Pengambilan Air Tanah',
                'code' => 'IPAT',
                'description' => 'Retribusi untuk pengambilan dan pemanfaatan air tanah/air permukaan. Dasar hukum: Perda setempat tentang pengelolaan air tanah.',
                'asset_category_id' => $categories['instalasi_air']?->id,
                'period_type' => 'yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 50000.00,
                'penalty_type' => 'fixed',
                'penalty_period' => 'monthly',
                'reminder_days' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Retribusi Izin Lingkungan (AMDAL/UKL-UPL)',
                'code' => 'AMDAL',
                'description' => 'Retribusi untuk izin lingkungan instalasi pengolahan air atau fasilitas lain yang berdampak lingkungan. Dasar hukum: UU No. 32 Tahun 2009 tentang Perlindungan dan Pengelolaan Lingkungan Hidup.',
                'asset_category_id' => $categories['ipa_ipam']?->id,
                'period_type' => 'custom',
                'period_months' => 36, // 3 tahun
                'has_penalty' => true,
                'penalty_percentage' => 100000.00,
                'penalty_type' => 'fixed',
                'penalty_period' => 'monthly',
                'reminder_days' => 90,
                'is_active' => true,
            ],

            // ========================================
            // PAJAK PENGHASILAN (PPh) - Jika Relevan
            // ========================================
            [
                'name' => 'PPh Pasal 4 Ayat 2 (Sewa Tanah/Bangunan)',
                'code' => 'PPH-SEWA',
                'description' => 'Pajak penghasilan atas sewa tanah dan/atau bangunan. Tarif 10% dari nilai sewa bruto (bersifat final). Dasar hukum: PP No. 34 Tahun 2017.',
                'asset_category_id' => $categories['bangunan']?->id,
                'period_type' => 'yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 2.0,
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 30,
                'is_active' => false, // Nonaktif default, aktifkan jika aset disewakan
            ],
        ];

        // Seed data ke database
        foreach ($taxTypes as $taxType) {
            MasterTaxType::updateOrCreate(
                ['code' => $taxType['code']],
                $taxType
            );
        }

        $this->command->info('✅ Tax types seeded successfully!');
        $this->command->info('📊 Total: ' . count($taxTypes) . ' jenis pajak telah ditambahkan.');
        $this->command->line('');
        $this->command->info('Jenis Pajak yang ditambahkan:');
        $this->command->line('- Pajak Kendaraan: PKB, BBNKB, SWDKLLJ');
        $this->command->line('- Pajak Tanah & Bangunan: PBB, BPHTB');
        $this->command->line('- Retribusi Bangunan: PBG, SLF');
        $this->command->line('- Retribusi Lingkungan: IPAT, AMDAL');
        $this->command->line('- PPh: PPh Sewa (nonaktif default)');
    }
}
