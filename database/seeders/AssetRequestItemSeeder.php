<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AssetRequests;
use App\Models\AssetRequestItem;
use App\Models\MasterAssetsCategory;
use App\Models\MasterAssetsLocation;
use App\Models\MasterDepartments;
use App\Models\User;

class AssetRequestItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data master yang diperlukan
        $categories = MasterAssetsCategory::all();
        $locations = MasterAssetsLocation::all();
        $departments = MasterDepartments::all();
        $users = User::all();

        if ($categories->isEmpty() || $locations->isEmpty() || $departments->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Pastikan sudah ada data master: Category, Location, Department, dan User');
            return;
        }

        // Contoh Request 1: Multiple items (3 items berbeda)
        $request1 = AssetRequests::create([
            'document_number' => 'PR-' . now()->format('Ym') . '-0001',
            'date' => now(),
            'department_id' => $departments->random()->id,
            'requested_by' => $users->random()->id,
            'desc' => 'Pengadaan peralatan IT untuk staff baru',
            'total_items' => 3,
            'total_quantity' => 8,
            'kepala_sub_bagian' => true,
            'kepala_bagian_umum' => true,
            'kepala_bagian_keuangan' => false,
            'direktur_umum' => false,
            'direktur_utama' => false,
            'docs' => 'bukti-permintaan/sample.jpg',
            'users_id' => $users->first()->id,
            'status_request' => true,
            'purchase_status' => 'pending',
        ]);

        // Items untuk Request 1
        AssetRequestItem::create([
            'asset_request_id' => $request1->id,
            'asset_name' => 'Laptop Dell Latitude 5420',
            'category_id' => $categories->where('name', 'like', '%Komputer%')->first()?->id ?? $categories->random()->id,
            'location_id' => $locations->random()->id,
            'sub_location_id' => null,
            'quantity' => 3,
            'purpose' => 'Untuk staff baru divisi IT',
            'notes' => 'Spek: i5, RAM 16GB, SSD 512GB',
        ]);

        AssetRequestItem::create([
            'asset_request_id' => $request1->id,
            'asset_name' => 'Monitor LG 24 Inch',
            'category_id' => $categories->where('name', 'like', '%Monitor%')->first()?->id ?? $categories->random()->id,
            'location_id' => $locations->random()->id,
            'sub_location_id' => null,
            'quantity' => 3,
            'purpose' => 'Monitor tambahan untuk staff IT',
            'notes' => 'Full HD IPS',
        ]);

        AssetRequestItem::create([
            'asset_request_id' => $request1->id,
            'asset_name' => 'Keyboard Logitech Wireless',
            'category_id' => $categories->random()->id,
            'location_id' => $locations->random()->id,
            'sub_location_id' => null,
            'quantity' => 2,
            'purpose' => 'Keyboard tambahan',
            'notes' => null,
        ]);

        // Contoh Request 2: Single item dengan quantity banyak
        $request2 = AssetRequests::create([
            'document_number' => 'PR-' . now()->format('Ym') . '-0002',
            'date' => now()->subDays(5),
            'department_id' => $departments->random()->id,
            'requested_by' => $users->random()->id,
            'desc' => 'Pengadaan kursi kantor untuk ruang meeting',
            'total_items' => 1,
            'total_quantity' => 10,
            'kepala_sub_bagian' => true,
            'kepala_bagian_umum' => true,
            'kepala_bagian_keuangan' => true,
            'direktur_umum' => true,
            'direktur_utama' => false,
            'docs' => 'bukti-permintaan/sample2.jpg',
            'users_id' => $users->first()->id,
            'status_request' => true,
            'purchase_status' => 'pending',
        ]);

        AssetRequestItem::create([
            'asset_request_id' => $request2->id,
            'asset_name' => 'Kursi Kantor Ergonomis',
            'category_id' => $categories->random()->id,
            'location_id' => $locations->first()->id,
            'sub_location_id' => null,
            'quantity' => 10,
            'purpose' => 'Untuk ruang meeting lantai 3',
            'notes' => 'Warna hitam, dengan sandaran tinggi',
        ]);

        // Contoh Request 3: Multiple items dengan lokasi berbeda
        $request3 = AssetRequests::create([
            'document_number' => 'PR-' . now()->format('Ym') . '-0003',
            'date' => now()->subDays(2),
            'department_id' => $departments->random()->id,
            'requested_by' => $users->random()->id,
            'desc' => 'Pengadaan AC untuk beberapa ruangan',
            'total_items' => 2,
            'total_quantity' => 5,
            'kepala_sub_bagian' => true,
            'kepala_bagian_umum' => false,
            'kepala_bagian_keuangan' => false,
            'direktur_umum' => false,
            'direktur_utama' => false,
            'docs' => 'bukti-permintaan/sample3.jpg',
            'users_id' => $users->first()->id,
            'status_request' => false,
            'purchase_status' => 'pending',
        ]);

        AssetRequestItem::create([
            'asset_request_id' => $request3->id,
            'asset_name' => 'AC Split 1.5 PK',
            'category_id' => $categories->random()->id,
            'location_id' => $locations->first()->id,
            'sub_location_id' => null,
            'quantity' => 3,
            'purpose' => 'Untuk ruang server',
            'notes' => 'Low watt, inverter',
        ]);

        AssetRequestItem::create([
            'asset_request_id' => $request3->id,
            'asset_name' => 'AC Split 2 PK',
            'category_id' => $categories->random()->id,
            'location_id' => $locations->last()->id,
            'sub_location_id' => null,
            'quantity' => 2,
            'purpose' => 'Untuk ruang meeting besar',
            'notes' => 'Sharp atau Daikin',
        ]);

        $this->command->info('✅ AssetRequestItem seeder berhasil! 3 requests dengan total 6 items dibuat.');
    }
}
