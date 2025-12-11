<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetSubLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user ID (sama seperti seeder sebelumnya)
        $userId = DB::table('users')->value('id');

        // Ambil ID dari "Ruang Kantor Pusat" (kode 01)
        $kantorPusat = DB::table('master_assets_locations')
            ->where('kode', '01')
            ->orWhere('name', 'Ruang Kantor Pusat')
            ->first();

        if (!$kantorPusat) {
            // Jika entah kenapa belum ada, skip atau beri warning
            return;
        }

        $locationId = $kantorPusat->id;

        // Daftar sub-lokasi khusus untuk Kantor Pusat (kode 01)
        $subLocations = [
            // Ruang Informasi Teknologi
            'Ruang Server',
            'Ruang Lab',
            'Ruang Kerja IT',

            // Ruang Rapat GIS
            'Ruang Rapat GIS',

            // Ruang Loket
            'Ruang Loket Pembayaran',
            'Ruang Loket Pengaduan dan Pendaftaran',

            // Ruang Dewan Pengawas
            'Ruang Dewan Pengawas',

            // Ruang Hubungan Langganan
            'Ruang Kepala Bagian Hubungan Langganan',
            'Ruang Kerja Hubungan Langganan',

            // Ruang Hukum dan Humas
            'Ruang Hukum dan Humas',

            // Ruang SPI
            'Ruang SPI',

            // Ruang Direktur
            'Ruang Direktur Umum',
            'Ruang Direktur Utama',

            // Ruang Sekretaris
            'Ruang Sekretaris',

            // Ruang Umum dan Keuangan
            'Ruang Sub Bagian Kerumahtanggaan',
            'Ruang Sub Bagian Personalia',
            'Ruang Sub Bagian Anggaran Pendapatan',
            'Ruang Sub Bagian Verifikasi Pembukuan',
            'Ruang Kepala Bagian Umum',
            'Ruang Kepala Bagian Keuangan',

            // Ruang Teknik
            'Ruang Kepala Bagian Teknik',
            'Ruang Sub Bagian GIS dan NRW',
            'Ruang Sub Bagian Produksi',
            'Ruang Sub Bagian Transmisi dan Distribusi',
            'Ruang Sub Bagian Perencanaan',

            // Ruang Rapat Utama
            'Ruang Rapat Utama',

            // Ruang Mushola
            'Ruang Mushola',

            // Ruang Gudang
            'Ruang Gudang',

            // Ruang Security
            'Ruang Security',
        ];

        foreach ($subLocations as $name) {
            DB::table('master_assets_sub_locations')->insert([
                'id'         => Str::uuid(),
                'location_id' => $locationId,
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
                'users_id'   => $userId,
            ]);
        }
    }
}
