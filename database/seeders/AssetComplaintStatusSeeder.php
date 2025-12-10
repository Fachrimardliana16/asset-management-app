<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetComplaintStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $statuses = [
            ['name' => 'Dilaporkan', 'desc' => ''],
            ['name' => 'Diterima', 'desc' => ''],
            ['name' => 'Dikerjakan', 'desc' => ''],
            ['name' => 'Selesai', 'desc' => ''],
        ];

        foreach ($statuses as $status) {
            DB::table('master_assets_complaint_status')->insert([
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
