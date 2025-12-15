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
        Schema::table('assets_disposals', function (Blueprint $table) {
            // Add new approval fields
            $table->uuid('petugas_id')->nullable()->after('employee_id');
            $table->uuid('kepala_sub_bagian_id')->nullable()->after('petugas_id');
            $table->uuid('direktur_id')->nullable()->after('kepala_sub_bagian_id');
            
            // Add foreign key constraints
            $table->foreign('petugas_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('kepala_sub_bagian_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('direktur_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets_disposals', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['petugas_id']);
            $table->dropForeign(['kepala_sub_bagian_id']);
            $table->dropForeign(['direktur_id']);
            
            // Drop columns
            $table->dropColumn(['petugas_id', 'kepala_sub_bagian_id', 'direktur_id']);
        });
    }
};
