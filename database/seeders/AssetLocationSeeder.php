<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $locations = [

            //Kantor Cabang
            ['kode' => '01', 'name' => 'Ruang Kantor Pusat', 'desc' => ''],
            ['kode' => '02', 'name' => 'Ruang Kantor Unit IKK Bukateja', 'desc' => ''],
            ['kode' => '03', 'name' => 'Ruang Kantor Unit IKK Bobotsari', 'desc' => ''],
            ['kode' => '04', 'name' => 'Ruang Kantor Unit IKK Kutasari', 'desc' => ''],
            ['kode' => '05', 'name' => 'Ruang Kantor Unit IKK Bojongsari', 'desc' => ''],
            ['kode' => '06', 'name' => 'Ruang Kantor Unit IKK Mrebet', 'desc' => ''],
            ['kode' => '07', 'name' => 'Ruang Kantor Unit IKK Kemangkon', 'desc' => ''],
            ['kode' => '08', 'name' => 'Ruang Kantor Unit IKK Kejobong', 'desc' => ''],
            ['kode' => '09', 'name' => 'Ruang Kantor Unit IKK Rembang', 'desc' => ''],
            ['kode' => '10', 'name' => 'Ruang Kantor Unit IKK Unit AMDK', 'desc' => ''],
            ['kode' => '12', 'name' => 'Ruang Kantor Unit IKK Padamara', 'desc' => ''],
            ['kode' => '13', 'name' => 'Ruang Kantor Unit IKK Kalimanah', 'desc' => ''],
            ['kode' => '14', 'name' => 'Ruang Kantor Unit IKK Karangreja', 'desc' => ''],
            ['kode' => '15', 'name' => 'Ruang Kantor Unit IKK Kaligondang', 'desc' => ''],
            ['kode' => '16', 'name' => 'Ruang Kantor Cabang Kota', 'desc' => ''],
            ['kode' => '17', 'name' => 'Ruang Kantor Cabang Jendral Soedirman', 'desc' => ''],

        ];

        foreach ($locations as $location) {
            DB::table('master_assets_locations')->insert([
                'id' => Str::uuid(),
                'kode' => $location['kode'],
                'name' => $location['name'],
                'desc' => $location['desc'],
                'created_at' => now(),
                'updated_at' => now(),
                'users_id' => $userId,
            ]);
        }
    }
}
