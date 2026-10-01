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
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('work_location_type', ['pusat', 'cabang'])->default('pusat')->after('nippam');
            $table->uuid('cabang_unit_id')->nullable()->after('sub_department_id');

            $table->foreign('cabang_unit_id')->references('id')->on('master_cabang_unit')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['cabang_unit_id']);
            $table->dropColumn(['work_location_type', 'cabang_unit_id']);
        });
    }
};
