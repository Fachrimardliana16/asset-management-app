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
        // Get or create default department
        $hublangDepartment = MasterDepartments::where('name', 'Bagian Hubungan Langganan')->first();
        $umumDepartment = MasterDepartments::where('name', 'Bagian Umum')->first();
        $keuanganDepartment = MasterDepartments::where('name', 'Bagian Keuangan')->first();
        $teknikDepartment = MasterDepartments::where('name', 'Bagian Teknik')->first();
        $spiDepartment = MasterDepartments::where('name', 'Bagian SPI')->first();
        //cabang
        $jensoedDepartment = MasterDepartments::where('name', 'Cabang Jendral Soedirman')->first();
        $usmanJanatinDepartment = MasterDepartments::where('name', 'Cabang Usman Janatin')->first();
        $ardilawetDepartment = MasterDepartments::where('name', 'Cabang Ardi Lawet')->first();
        $goentoerDjarjonoDepartment = MasterDepartments::where('name', 'Cabang Goentoer Djarjono')->first();
        $kotaBanggaDepartment = MasterDepartments::where('name', 'Cabang Kota Bangga')->first();
        //unit
        $unitkemangkonDepartment = MasterDepartments::where('name', 'Unit IKK Kemangkon')->first();
        $unitbukatejaDepartment = MasterDepartments::where('name', 'Unit IKK Bukateja')->first();
        $unitkarangrejaDepartment = MasterDepartments::where('name', 'Unit IKK Karangreja')->first();
        $unitrembangDepartment = MasterDepartments::where('name', 'Unit IKK Rembang')->first();

        // Get or create default sub department
        //keuangan sub bagian
        $gudangSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Gudang')->first();
        $verifikasiPembukuanSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Verifikasi Pembukuan')->first();
        $anggaranPendapatanSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Anggaran dan Pendapatan')->first();

        //umum sub bagian
        $kepegawaianSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Kepegawaian')->first();
        $kerumahTanggaanSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Kerumah Tanggaan')->first();
        $itSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Informasi Teknologi')->first();
        $kesekretariatanArsipSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Kesekertariatan dan Arsip')->first();
        $hukumHumasSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Hukum dan Humas')->first();

        //teknik sub bagian
        $gisNrwSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian GIS dan NRW')->first();
        $perencanaanLitbangSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Perencanaan dan Litbang')->first();
        $transmisiDistribusiSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Transmisi dan Distribusi')->first();
        $produksiSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Produksi')->first();

        //hublang sub bagian
        $pemasaranSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Pemasaran')->first();
        $pelayananLanggananSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Pelayanan Langganan')->first();
        $bacaMeterSubDepartment = MasterSubDepartments::where('name', 'Sub Bagian Baca Meter')->first();


        // Get positions
        $staffPosition = MasterEmployeePosition::where('name', 'Staff')->first();
        $kasubagPosition = MasterEmployeePosition::where('name', 'Kepala Sub Bagian')->first();
        $kacabPosition = MasterEmployeePosition::where('name', 'Kepala Cabang')->first();
        $kanitPosition = MasterEmployeePosition::where('name', 'Kepala Unit')->first();
        $kabagPosition = MasterEmployeePosition::where('name', 'Kepala Bagian')->first();
        $direksiPosition = MasterEmployeePosition::where('name', 'Direksi')->first();

        $employees = [
            [
                'nippam' => 'EMP000',
                'name' => 'Sugeng, S.T',
                'departments_id' => null,
                'sub_department_id' => null,
                'employee_position_id' => $direksiPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1990-05-15',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'sugeng@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP001',
                'name' => 'Baryono, S.H',
                'departments_id' => null,
                'sub_department_id' => null,
                'employee_position_id' => $direksiPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1990-05-15',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'baryono@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP003',
                'name' => 'Endah Susilowati, S.H',
                'departments_id' => $umumDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kabagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1990-05-15',
                'gender' => 'female',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'endah@pdampurbalingga.co.id',
            ],
            [
                'nippam' => 'EMP004',
                'name' => 'Yuni Setyowati, S.E',
                'departments_id' => $keuanganDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kabagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1988-08-20',
                'gender' => 'female',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'yuni.setyowai@pdampurbalingga.co.id',
            ],
            [
                'nippam' => 'EMP005',
                'name' => 'Widiasmoko, S.T',
                'departments_id' => $teknikDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kabagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1985-03-10',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'widiasmoko@pdampurbalingga.co.id',
            ],
            [
                'nippam' => 'EMP006',
                'name' => 'Triyono',
                'departments_id' => $spiDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kabagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'triyono@pdampubalingga.co.id',
            ],

            [
                'nippam' => 'EMP007',
                'name' => 'Kusumo Satrio Utomo, S.T',
                'departments_id' => $umumDepartment?->id,
                'sub_department_id' => $kerumahTanggaanSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'kusumo@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP008',
                'name' => 'Susi Herawati',
                'departments_id' => $umumDepartment?->id,
                'sub_department_id' => $kesekretariatanArsipSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'female',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'susi@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP009',
                'name' => 'Yuni Nurhayah',
                'departments_id' => $umumDepartment?->id,
                'sub_department_id' => $kepegawaianSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'female',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'yuni@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP010',
                'name' => 'Anggrian Wedha, S.H',
                'departments_id' => $umumDepartment?->id,
                'sub_department_id' => $hukumHumasSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'anggrian@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP011',
                'name' => 'Iwan Infantri, S.E',
                'departments_id' => $keuanganDepartment?->id,
                'sub_department_id' => $anggaranPendapatanSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'iwan@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP012',
                'name' => 'Andjar Iswanto, S.Kom',
                'departments_id' => $keuanganDepartment?->id,
                'sub_department_id' => $verifikasiPembukuanSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'andjar@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP013',
                'name' => 'Destara Arnas',
                'departments_id' => $keuanganDepartment?->id,
                'sub_department_id' => $gudangSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'destara@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP014',
                'name' => 'Imam Toni',
                'departments_id' => $hublangDepartment?->id,
                'sub_department_id' => $pelayananLanggananSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'imamtoni@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP015',
                'name' => 'Agung Cahyadi',
                'departments_id' => $hublangDepartment?->id,
                'sub_department_id' => $bacaMeterSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'agung@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP016',
                'name' => 'Berliana Diah',
                'departments_id' => $hublangDepartment?->id,
                'sub_department_id' => $pemasaranSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'female',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'berliana@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP017',
                'name' => 'Santoso',
                'departments_id' => $teknikDepartment?->id,
                'sub_department_id' => $gisNrwSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'santoso@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP018',
                'name' => 'Sugeng, A.md',
                'departments_id' => $teknikDepartment?->id,
                'sub_department_id' => $perencanaanLitbangSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'sugengamd@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP019',
                'name' => 'Supandi, A.md',
                'departments_id' => $teknikDepartment?->id,
                'sub_department_id' => $produksiSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'supandi@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP020',
                'name' => 'sopandi',
                'departments_id' => $teknikDepartment?->id,
                'sub_department_id' => $transmisiDistribusiSubDepartment?->id,
                'employee_position_id' => $kasubagPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'sopandi@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP021',
                'name' => 'Maun Suseno',
                'departments_id' => $unitkemangkonDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kanitPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'maun@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP022',
                'name' => 'Suyitno',
                'departments_id' => $unitbukatejaDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kanitPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'suyitno@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP023',
                'name' => 'Margono',
                'departments_id' => $unitrembangDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kanitPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'margono@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP024',
                'name' => 'Riyadi',
                'departments_id' => $unitkarangrejaDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kanitPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'riyadi@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP025',
                'name' => 'Sutarman',
                'departments_id' => $jensoedDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kacabPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'sutarman@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP026',
                'name' => 'Teguh Kunjtoro',
                'departments_id' => $ardilawetDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kacabPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'teguh@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP027',
                'name' => 'Adik Purwo',
                'departments_id' => $goentoerDjarjonoDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kacabPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'adik@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP028',
                'name' => 'Rakhmanto, S.St',
                'departments_id' => $usmanJanatinDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kacabPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'rakhmanto@pdampurbalingga.co.id',
            ],

            [
                'nippam' => 'EMP029',
                'name' => 'Tur Tjahtojo',
                'departments_id' => $kotaBanggaDepartment?->id,
                'sub_department_id' => null,
                'employee_position_id' => $kacabPosition?->id,
                'place_birth' => 'Purbalingga',
                'date_birth' => '1975-12-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Purbalingga',
                'phone_number' => '891706',
                'email' => 'tur@pdampurbalingga.co.id',
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
