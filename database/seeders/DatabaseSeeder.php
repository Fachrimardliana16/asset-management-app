<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            DepartmentsSeeder::class,
            SubDepartmentsSeeder::class,
            MasterEmployeePositionSeeder::class,
            EmployeeSeeder::class,
            AssetLocationSeeder::class,
            AssetSubLocationSeeder::class,
            AssetConditionSeeder::class,
            AssetComplaintStatusSeeder::class,
            AssetTransactionStatusSeeder::class,
            AssetCategorySeeder::class,
            AssetsStatusSeeder::class,
            MasterBranchOfficeSeeder::class,
            MasterBranchUnitSeeder::class,
        ]);

        // Generate permissions for all resources
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
        ]);
    }
}
