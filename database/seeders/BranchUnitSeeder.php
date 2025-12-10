<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userId = DB::table('users')->value('id');
        $units = [
            ['name' => 'Bukateja', 'desc' => ''],
            ['name' => 'Kemangkon', 'desc' => ''],
            ['name' => 'Rembang', 'desc' => ''],
            ['name' => 'Bobotsari', 'desc' => ''],
            ['name' => 'Karangreja', 'desc' => ''],
            ['name' => 'Mrebet', 'desc' => ''],
            ['name' => 'Bojongsari', 'desc' => ''],
            ['name' => 'Kutasari', 'desc' => ''],
            ['name' => 'Padamara', 'desc' => ''],
            ['name' => 'Kalimanah', 'desc' => '']
        ];

        foreach ($units as $unit) {
            DB::table('master_branch_unit')->insert([
                'id' => Str::uuid(),
                'name' => $unit['name'],
                'desc' => $unit['desc'],
                'created_at' => now(),
                'updated_at' => now(),
                'users_id' => $userId,
            ]);
        }
    }
}
