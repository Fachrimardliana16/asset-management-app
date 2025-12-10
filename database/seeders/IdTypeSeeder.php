<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IdTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $data = [
            ['name' => 'KTP', 'desc' => ''],
            ['name' => 'KK', 'desc' => ''],
            ['name' => 'SIM', 'desc' => ''],
            ['name' => 'Passport', 'desc' => '']
        ];

        foreach ($data as $data) {
            DB::table('master_billing_idtypes')->insert([
                'id' => Str::uuid(),
                'name' => $data['name'],
                'desc' => $data['desc'],
                'created_at' => now(),
                'updated_at' => now(),
                'users_id' => $userId,
            ]);
        }
    }
}
