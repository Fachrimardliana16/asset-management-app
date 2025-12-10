<?php

namespace Database\Seeders;

use App\Models\MasterEmployeePosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterEmployeePositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'name' => 'Staff',
                'desc' => 'Pegawai staff operasional',
            ],
            [
                'name' => 'Kepala Sub Bagian',
                'desc' => 'Kepala sub bagian/unit kerja',
            ],
            [
                'name' => 'Kepala Bagian',
                'desc' => 'Kepala bagian/divisi',
            ],
            [
                'name' => 'Direksi',
                'desc' => 'Jajaran direksi perusahaan',
            ],
        ];

        foreach ($positions as $position) {
            MasterEmployeePosition::updateOrCreate(
                ['name' => $position['name']],
                [
                    'desc' => $position['desc'],
                    'users_id' => null,
                ]
            );
        }
    }
}
