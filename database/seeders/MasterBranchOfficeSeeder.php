<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterBranchOfficeSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->inRandomOrder()->value('id');
        if (!$userId) {
            echo "Peringatan: Users table kosong.\n";
            return;
        }

        $officesToSeed = [
            'Cabang Kota Bangga',
            'Cabang Jendral Soedirman',
            'Cabang Usman Janatin',
            'Cabang Ardilawet',
            'Cabang Goentoer Djarjono',
            'Cabang INDUK (Unit Mandiri)',
        ];

        foreach ($officesToSeed as $index => $name) {
            $code = 'CBG.' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);

            // GANTI firstOrCreate() dengan updateOrInsert()
            DB::table('master_branch_office')->updateOrInsert(
                ['name' => $name], // Kondisi pencarian (jika ada, jangan buat)
                [
                    'id' => Str::uuid(), // Harus selalu baru karena ini UUID
                    'code' => $code,
                    'users_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
