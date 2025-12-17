<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets_requests', function (Blueprint $table) {
            // Hapus kolom item-specific (pindah ke asset_request_items)
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'asset_name',
                'category_id',
                'quantity',
                'purpose',
            ]);

            // Hapus kolom location yang dipindah ke items
            if (Schema::hasColumn('assets_requests', 'employee_id')) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            }
            if (Schema::hasColumn('assets_requests', 'location_id')) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            }
            if (Schema::hasColumn('assets_requests', 'sub_location_id')) {
                $table->dropForeign(['sub_location_id']);
                $table->dropColumn('sub_location_id');
            }

            // Tambah kolom summary
            $table->integer('total_items')->default(0)->after('date'); // jumlah jenis barang
            $table->integer('total_quantity')->default(0)->after('total_items'); // total unit
            $table->uuid('department_id')->nullable()->after('total_quantity'); // department pemohon
            $table->uuid('requested_by')->nullable()->after('department_id'); // user pemohon

            // Foreign keys untuk kolom baru
            $table->foreign('department_id')->references('id')->on('master_departments')->onDelete('set null');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets_requests', function (Blueprint $table) {
            // Kembalikan kolom yang dihapus
            $table->string('asset_name')->nullable();
            $table->uuid('category_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('purpose')->nullable();
            $table->uuid('employee_id')->nullable();
            $table->uuid('location_id')->nullable();
            $table->uuid('sub_location_id')->nullable();

            // Kembalikan foreign keys
            $table->foreign('category_id')->references('id')->on('master_assets_category');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('location_id')->references('id')->on('master_assets_locations');
            $table->foreign('sub_location_id')->references('id')->on('master_assets_sub_locations');

            // Hapus kolom summary
            $table->dropForeign(['department_id']);
            $table->dropForeign(['requested_by']);
            $table->dropColumn([
                'total_items',
                'total_quantity',
                'department_id',
                'requested_by'
            ]);
        });
    }
};
