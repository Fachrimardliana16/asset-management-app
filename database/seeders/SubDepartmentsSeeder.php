<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubDepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $departments = DB::table('master_departments')->get();
        $userId = DB::table('users')->value('id');
        $subDepartments = [
            // Bagian Keuangan
            ['department_name' => 'Bagian Keuangan', 'sub_department_name' => 'Sub Bagian Anggaran dan Pendapatan'],
            ['department_name' => 'Bagian Keuangan', 'sub_department_name' => 'Sub Bagian Verifikasi Pembukuan'],

            // Bagian Umum
            ['department_name' => 'Bagian Umum', 'sub_department_name' => 'Sub Bagian Personalian'],
            ['department_name' => 'Bagian Umum', 'sub_department_name' => 'Sub Bagian Kerumah Tanggaan'],
            ['department_name' => 'Bagian Umum', 'sub_department_name' => 'Sub Bagian Informasi Teknologi'],
            ['department_name' => 'Bagian Umum', 'sub_department_name' => 'Sub Bagian Kesekertariatan dan Arsip'],
            ['department_name' => 'Bagian Umum', 'sub_department_name' => 'Sub Bagian Hukum dan Humas'],

            // Bagian Teknik
            ['department_name' => 'Bagian Teknik', 'sub_department_name' => 'Sub Bagian GIS dan NRW'],
            ['department_name' => 'Bagian Teknik', 'sub_department_name' => 'Sub Bagian Perencanaan dan Litbang'],
            ['department_name' => 'Bagian Teknik', 'sub_department_name' => 'Sub Bagian Transmisi dan Distribusi'],
            ['department_name' => 'Bagian Teknik', 'sub_department_name' => 'Sub Bagian Produksi'],

            // Bagian Hubungan Langganan
            ['department_name' => 'Bagian Hubungan Langganan', 'sub_department_name' => 'Sub Bagian Pemasaran'],
            ['department_name' => 'Bagian Hubungan Langganan', 'sub_department_name' => 'Sub Bagian Pelayanan Langganan'],
            ['department_name' => 'Bagian Hubungan Langganan', 'sub_department_name' => 'Sub Bagian Baca Meter'],
        ];

        foreach ($subDepartments as $subDepartment) {
            $department = $departments->firstWhere('name', $subDepartment['department_name']);
            if ($department) {
                DB::table('master_sub_departments')->insert([
                    'id' => Str::uuid(),
                    'departments_id' => $department->id,
                    'name' => $subDepartment['sub_department_name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'users_id' => $userId,
                ]);
            }
        }
    }
}
