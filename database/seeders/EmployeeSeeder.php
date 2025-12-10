<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\MasterDepartments;
use App\Models\MasterSubDepartments;
use App\Models\MasterEmployeePosition;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create default department and position
        $department = MasterDepartments::first();
        $subDepartment = MasterSubDepartments::first();

        // Get positions
        $staffPosition = MasterEmployeePosition::where('name', 'Staff')->first();
        $kasubagPosition = MasterEmployeePosition::where('name', 'Kepala Sub Bagian')->first();
        $kabagPosition = MasterEmployeePosition::where('name', 'Kepala Bagian')->first();
        $direksiPosition = MasterEmployeePosition::where('name', 'Direksi')->first();

        $employees = [
            [
                'nippam' => 'EMP001',
                'name' => 'Budi Santoso',
                'departments_id' => $department?->id,
                'sub_department_id' => $subDepartment?->id,
                'employee_position_id' => $staffPosition?->id,
                'place_birth' => 'Jakarta',
                'date_birth' => '1990-05-15',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'phone_number' => '081234567890',
                'email' => 'budi.santoso@company.com',
            ],
            [
                'nippam' => 'EMP002',
                'name' => 'Siti Nurhaliza',
                'departments_id' => $department?->id,
                'sub_department_id' => $subDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Bandung',
                'date_birth' => '1988-08-20',
                'gender' => 'female',
                'religion' => 'Islam',
                'address' => 'Jl. Asia Afrika No. 25, Bandung',
                'phone_number' => '081234567891',
                'email' => 'siti.nurhaliza@company.com',
            ],
            [
                'nippam' => 'EMP003',
                'name' => 'Ahmad Wijaya',
                'departments_id' => $department?->id,
                'sub_department_id' => $subDepartment?->id,
                'employee_position_id' => $kabagPosition?->id,
                'place_birth' => 'Surabaya',
                'date_birth' => '1985-03-10',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Jl. Tunjungan No. 50, Surabaya',
                'phone_number' => '081234567892',
                'email' => 'ahmad.wijaya@company.com',
            ],
            [
                'nippam' => 'EMP004',
                'name' => 'Dr. Rini Handayani',
                'departments_id' => $department?->id,
                'sub_department_id' => $subDepartment?->id,
                'employee_position_id' => $direksiPosition?->id,
                'place_birth' => 'Yogyakarta',
                'date_birth' => '1975-12-01',
                'gender' => 'female',
                'religion' => 'Islam',
                'address' => 'Jl. Malioboro No. 100, Yogyakarta',
                'phone_number' => '081234567893',
                'email' => 'rini.handayani@company.com',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::updateOrCreate(
                ['nippam' => $employee['nippam']],
                $employee
            );
        }
    }
}
