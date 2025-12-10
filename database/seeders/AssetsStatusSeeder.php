<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetsStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $status = [
            ['name' => 'Active', 'desc' => ''],
            ['name' => 'Inactive', 'desc' => ''],
        ];

        foreach ($status as $status) {
            DB::table('master_assets_status')->insert([
                'id' => Str::uuid(),
                'name' => $status['name'],
                'desc' => $status['desc'],
                'created_at' => now(),
                'updated_at' => now(),
                'users_id' => $userId,
            ]);
        }
    }
}
