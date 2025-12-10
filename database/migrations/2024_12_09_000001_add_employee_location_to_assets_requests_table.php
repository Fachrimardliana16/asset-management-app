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
            $table->uuid('employee_id')->nullable()->after('category_id');
            $table->uuid('location_id')->nullable()->after('employee_id');
            $table->uuid('sub_location_id')->nullable()->after('location_id');

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('master_assets_locations')->onDelete('set null');
            $table->foreign('sub_location_id')->references('id')->on('master_assets_sub_locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets_requests', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['sub_location_id']);
            $table->dropColumn(['employee_id', 'location_id', 'sub_location_id']);
        });
    }
};
