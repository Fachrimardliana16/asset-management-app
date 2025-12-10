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
        Schema::table('asset_purchases', function (Blueprint $table) {
            $table->uuid('employee_id')->nullable()->after('category_id');
            $table->uuid('location_id')->nullable()->after('employee_id');
            $table->uuid('sub_location_id')->nullable()->after('location_id');
            $table->uuid('status_id')->nullable()->after('condition_id');
            $table->decimal('book_value', 15, 2)->default(0)->after('price');
            $table->date('book_value_expiry')->nullable()->after('book_value');
            $table->text('purchase_notes')->nullable()->after('funding_source');
            $table->integer('item_index')->default(1)->after('purchase_notes'); // Untuk tracking index dalam 1 permintaan

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('master_assets_locations')->onDelete('set null');
            $table->foreign('sub_location_id')->references('id')->on('master_assets_sub_locations')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('master_assets_status')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_purchases', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['sub_location_id']);
            $table->dropForeign(['status_id']);
            $table->dropColumn(['employee_id', 'location_id', 'sub_location_id', 'status_id', 'book_value', 'book_value_expiry', 'purchase_notes', 'item_index']);
        });
    }
};
