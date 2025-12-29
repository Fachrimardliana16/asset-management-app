<?php

namespace Database\Seeders;

use App\Models\MasterTaxType;
use App\Models\MasterAssetsCategory;
use Illuminate\Database\Seeder;

class TaxTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get kategori kendaraan
        $vehicleCategory = MasterAssetsCategory::where('name', 'like', '%kendaraan%')
            ->orWhere('name', 'like', '%vehicle%')
            ->first();

        // Get kategori tanah/bangunan
        $propertyCategory = MasterAssetsCategory::where('name', 'like', '%tanah%')
            ->orWhere('name', 'like', '%bangunan%')
            ->orWhere('name', 'like', '%property%')
            ->first();

        $taxTypes = [
            // Pajak Kendaraan
            [
                'name' => 'PKB (Pajak Kendaraan Bermotor)',
                'code' => 'PKB',
                'description' => 'Pajak tahunan untuk kendaraan bermotor',
                'asset_category_id' => $vehicleCategory?->id,
                'period_type' => 'yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 2.0, // 2% per bulan
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'BPKB (Bea Balik Nama Kendaraan Bermotor)',
                'code' => 'BPKB',
                'description' => 'Pajak untuk balik nama kendaraan bermotor',
                'asset_category_id' => $vehicleCategory?->id,
                'period_type' => 'custom',
                'period_months' => 60, // 5 tahun
                'has_penalty' => true,
                'penalty_percentage' => 1.5,
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'SWDKLLJ (Sumbangan Wajib Dana Kecelakaan Lalu Lintas Jalan)',
                'code' => 'SWDKLLJ',
                'description' => 'Iuran wajib kecelakaan lalu lintas',
                'asset_category_id' => $vehicleCategory?->id,
                'period_type' => 'yearly',
                'period_months' => null,
                'has_penalty' => false,
                'penalty_percentage' => null,
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 30,
                'is_active' => true,
            ],

            // Pajak Tanah & Bangunan
            [
                'name' => 'PBB (Pajak Bumi dan Bangunan)',
                'code' => 'PBB',
                'description' => 'Pajak tahunan untuk tanah dan bangunan',
                'asset_category_id' => $propertyCategory?->id,
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
                'name' => 'IMB (Izin Mendirikan Bangunan)',
                'code' => 'IMB',
                'description' => 'Retribusi izin mendirikan bangunan',
                'asset_category_id' => $propertyCategory?->id,
                'period_type' => '5yearly',
                'period_months' => null,
                'has_penalty' => true,
                'penalty_percentage' => 50000.00,
                'penalty_type' => 'fixed',
                'penalty_period' => 'monthly',
                'reminder_days' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'BPHTB (Bea Perolehan Hak atas Tanah dan Bangunan)',
                'code' => 'BPHTB',
                'description' => 'Pajak perolehan hak atas tanah dan bangunan',
                'asset_category_id' => $propertyCategory?->id,
                'period_type' => 'custom',
                'period_months' => 1,
                'has_penalty' => true,
                'penalty_percentage' => 2.0,
                'penalty_type' => 'percentage',
                'penalty_period' => 'monthly',
                'reminder_days' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($taxTypes as $taxType) {
            MasterTaxType::updateOrCreate(
                ['code' => $taxType['code']],
                $taxType
            );
        }

        $this->command->info('Tax types seeded successfully!');
    }
}
