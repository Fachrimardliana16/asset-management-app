<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // Password default yang di-hash
        $defaultPassword = Hash::make('pdam891706');

        // Peta peran dan email. KOREKSI: Menggunakan 'super_admin'.
        $usersToSeed = [
            'super_admin' => [ // Sudah dikoreksi: 'super_admin'
                ['email' => 'fachri@pdampurbalingga.co.id', 'firstname' => 'Fachri', 'lastname' => ''],
                ['email' => 'aulia@pdampurbalingga.co.id', 'firstname' => 'Aulia', 'lastname' => ''],
            ],
            'admin' => [
                // Email Kholiq sudah dikoreksi di sini
                ['email' => 'kholiq@pdampurbalingga.co.id', 'firstname' => 'Kholiq', 'lastname' => ''],
            ],
            'kasubag' => [
                ['email' => 'satrio@pdampurbalingga.co.id', 'firstname' => 'Satrio', 'lastname' => ''],
            ],
            'staff' => [
                ['email' => 'tyas@pdampurbalingga.co.id', 'firstname' => 'Tyas', 'lastname' => ''],
                ['email' => 'alwan@pdampurbalingga.co.id', 'firstname' => 'Alwan', 'lastname' => ''],
                ['email' => 'tiara@pdampurbalingga.co.id', 'firstname' => 'Tiara', 'lastname' => ''],
            ],
        ];

        // 1. Tambahkan pengguna superadmin bawaan (default starter-kit)
        $defaultSuperadminId = Str::uuid();
        DB::table('users')->insert([
            'id' => $defaultSuperadminId,
            'username' => 'superadmin',
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'email' => 'superadmin@starter-kit.com',
            'email_verified_at' => now(),
            'password' => Hash::make('superadmin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bind superadmin user to FilamentShield
        Artisan::call('shield:super-admin', ['--user' => $defaultSuperadminId]);

        // 2. Tambahkan pengguna PDAM yang diminta
        foreach ($usersToSeed as $roleName => $users) {
            // Ambil ID peran berdasarkan nama peran yang benar (termasuk 'super_admin')
            $role = DB::table('roles')->where('name', $roleName)->first();

            if ($role) {
                foreach ($users as $userData) {
                    $userId = Str::uuid();
                    $username = explode('@', $userData['email'])[0];

                    DB::table('users')->insert([
                        'id' => $userId,
                        'username' => $username,
                        'firstname' => $userData['firstname'],
                        'lastname' => $userData['lastname'] ?? '',
                        'email' => $userData['email'],
                        'email_verified_at' => now(),
                        'password' => $defaultPassword,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Tambahkan peran ke pengguna
                    DB::table('model_has_roles')->insert([
                        'role_id' => $role->id,
                        'model_type' => 'App\Models\User',
                        'model_id' => $userId,
                    ]);
                }
            } else {
                echo "Peringatan: Peran '$roleName' tidak ditemukan dalam database. Pastikan peran tersebut sudah di-seed.\n";
            }
        }
    }
}
