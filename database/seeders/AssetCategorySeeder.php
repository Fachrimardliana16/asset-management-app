<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id') ?? 1;

        $categories = [
            [
                'kode' => '3101',
                'name' => 'Tanah dan Penyempurnaan Tanah',
                'desc' => 'Tanah kavling, tanah untuk reservoir, sumur bor, galian arteis, pagar keliling, penimbunan, pengurukan, dan penyempurnaan lahan lainnya.',
            ],
            [
                'kode' => '3102',
                'name' => 'Instalasi Sumber Air',
                'desc' => 'Sumur bor dalam, sumur arteis, mata air, pompa sumber air, instalasi intake, pipa penyedot dari sungai/danau, dan bangunan pengambilan air baku.',
            ],
            [
                'kode' => '3103',
                'name' => 'Instalasi Pompa',
                'desc' => 'Pompa sentrifugal, pompa submersible, booster pump, pompa distribusi, motor listrik pompa, panel kontrol pompa, dan rumah pompa.',
            ],
            [
                'kode' => '3104',
                'name' => 'Instalasi Pengolahan Air (IPA/IPAM)',
                'desc' => 'Bak pengendap, bak filtrasi, bak penampung air bersih, tangki reservoir, toren air besar, instalasi kimia (dosing pump, tangki koagulan), bangunan IPA/IPAM.',
            ],
            [
                'kode' => '3105',
                'name' => 'Instalasi Transmisi dan Distribusi',
                'desc' => 'Pipa transmisi HDPE/besi, pipa distribusi PVC/HDPE, valve, hydrant, meter induk, meter rumah (SR), house connection, sambungan pipa, dan jaringan perpipaan.',
            ],
            [
                'kode' => '3106',
                'name' => 'Bangunan dan Gedung',
                'desc' => 'Gedung kantor pusat/cabang, gudang material, bengkel, mess karyawan, pos jaga, laboratorium air, dan bangunan tetap lainnya.',
            ],
            [
                'kode' => '3107',
                'name' => 'Peralatan dan Perlengkapan',
                'desc' => 'Komputer, laptop, printer, scanner, AC, genset, panel listrik, alat ukur (flowmeter, pressure gauge), alat laboratorium, alat deteksi kebocoran, dan peralatan operasional.',
            ],
            [
                'kode' => '3108',
                'name' => 'Kendaraan / Alat Angkut',
                'desc' => 'Mobil dinas, mobil tangki air, pickup operasional, motor dinas, sepeda motor lapangan, excavator mini, dan kendaraan/alat berat lainnya.',
            ],
            [
                'kode' => '3109',
                'name' => 'Inventaris / Perabot Kantor',
                'desc' => 'Meja, kursi, lemari arsip, filling cabinet, brankas, sofa, partisi ruangan, papan nama, dan perabot kantor lainnya.',
            ],
        ];

        foreach ($categories as $category) {
            DB::table('master_assets_category')->updateOrInsert(
                ['kode' => $category['kode']], // kalau kode sudah ada, update
                [
                    'id'         => Str::uuid(),
                    'kode'       => $category['kode'],
                    'name'       => $category['name'],
                    'desc'       => $category['desc'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'users_id'   => $userId,
                ]
            );
        }
    }
}
