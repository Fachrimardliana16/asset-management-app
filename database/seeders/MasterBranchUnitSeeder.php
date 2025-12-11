<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class MasterBranchUnitSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $userId = DB::table('users')->inRandomOrder()->value('id');
        if (!$userId) return;

        // --- 1. AMBIL ID CABANG UNTUK MAPPING ---
        $officeIds = DB::table('master_branch_office')
            ->pluck('id', 'name')
            ->toArray();

        // Data Akuntansi + Operasional (Ini hanya sebagian untuk contoh)
        $accountingUnits = [
            ['code' => '02', 'name' => 'Ruang Kantor Unit IKK Bukateja', 'office_name' => 'Cabang INDUK (Unit Mandiri)'],
            // ... masukkan semua data akuntansi Anda di sini ...
        ];

        // --- PROSES INSERT & MAPPING ---
        foreach ($accountingUnits as $data) {
            $branchOfficeId = $data['office_name'] ? ($officeIds[$data['office_name']] ?? null) : null;
            $description = $faker->sentence(5);

            // GANTI firstOrCreate() dengan updateOrInsert()
            DB::table('master_branch_unit')->updateOrInsert(
                ['accounting_code' => $data['code']], // Cari berdasarkan kode akuntansi
                [
                    'id' => Str::uuid(),
                    'name' => $data['name'],
                    'desc' => $description,
                    'users_id' => $userId,
                    'branch_office_id' => $branchOfficeId, // MAPPING
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
