<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterCabangUnitSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');

        $data = [
            ['kode' => 'CBG-001', 'nama' => 'Cabang Kota Bangga'],
            ['kode' => 'CBG-002', 'nama' => 'Cabang Jendral Soedirman'],
            ['kode' => 'CBG-003', 'nama' => 'Cabang Usman Janatin'],
            ['kode' => 'CBG-004', 'nama' => 'Cabang Goentoer Djarjono'],
            ['kode' => 'CBG-005', 'nama' => 'Cabang Ardilawet'],
            ['kode' => 'UNT-001', 'nama' => 'Unit IKK Kemangkon'],
            ['kode' => 'UNT-002', 'nama' => 'Unit IKK Rembang'],
            ['kode' => 'UNT-003', 'nama' => 'Unit IKK Karangreja'],
            ['kode' => 'UNT-004', 'nama' => 'Unit IKK Bukateja'],
        ];

        foreach ($data as $item) {
            DB::table('master_cabang_unit')->updateOrInsert(
                ['kode' => $item['kode']],
                [
                    'id'         => Str::uuid(),
                    'nama'       => $item['nama'],
                    'users_id'   => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
