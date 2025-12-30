<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $departments = [
            ['name' => 'Bagian Hubungan Langganan', 'desc' => ''],
            ['name' => 'Bagian Umum', 'desc' => ''],
            ['name' => 'Bagian Keuangan', 'desc' => ''],
            ['name' => 'Bagian Teknik', 'desc' => ''],
            ['name' => 'Bagian SPI', 'desc' => ''],
            ['name' => 'Unit IKK Kemangkon', 'desc' => ''],
            ['name' => 'Unit IKK Bukateja', 'desc' => ''],
            ['name' => 'Unit IKK Karangreja', 'desc' => ''],
            ['name' => 'Unit IKK Rembang', 'desc' => ''],
            ['name' => 'Cabang Usman Janatin', 'desc' => ''],
            ['name' => 'Cabang Kota Bangga', 'desc' => ''],
            ['name' => 'Cabang Ardi Lawet', 'desc' => ''],
            ['name' => 'Cabang Jendral Soedirman', 'desc' => ''],
            ['name' => 'Cabang Goentoer Djarjono', 'desc' => ''],


        ];

        foreach ($departments as $department) {
            DB::table('master_departments')->insert([
                'id' => Str::uuid(),
                'name' => $department['name'],
                'desc' => $department['desc'],
                'created_at' => now(),
                'updated_at' => now(),
                'users_id' => $userId,
            ]);
        }
    }
}
