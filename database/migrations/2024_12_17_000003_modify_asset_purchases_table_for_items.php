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
            // Tambah relasi ke asset_request_items
            $table->uuid('asset_request_item_id')->nullable()->after('assetrequest_id');
            
            // Foreign key
            $table->foreign('asset_request_item_id', 'fk_asset_purchases_request_item')
                ->references('id')
                ->on('asset_request_items')
                ->onDelete('cascade');

            // Update kolom img untuk memastikan bisa per-item
            // Kolom img sudah ada, jadi tidak perlu diubah
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_purchases', function (Blueprint $table) {
            $table->dropForeign('fk_asset_purchases_request_item');
            $table->dropColumn('asset_request_item_id');
        });
    }
};
