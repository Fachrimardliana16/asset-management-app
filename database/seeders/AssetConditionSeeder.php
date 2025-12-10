<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $conditions = [
            ['name' => 'Baru', 'desc' => ''],
            ['name' => 'Baik', 'desc' => ''],
            ['name' => 'Perlu Perbaikan', 'desc' => ''],
            ['name' => 'Sudah Diperbaiki', 'desc' => ''],
            ['name' => 'Rusak', 'desc' => ''],
        ];

        foreach ($conditions as $condition) {
            DB::table('master_assets_condition')->insert([
                'id' => Str::uuid(),
                'name' => $condition['name'],
                'desc' => $condition['desc'],
                'created_at' => now(),
                'updated_at' => now(),
                'users_id' => $userId,
            ]);
        }
    }
}
