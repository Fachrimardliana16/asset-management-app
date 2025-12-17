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
        Schema::create('asset_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_request_id');
            $table->string('asset_name');
            $table->uuid('category_id');
            $table->uuid('location_id');
            $table->uuid('sub_location_id')->nullable();
            $table->integer('quantity');
            $table->string('purpose'); // keperluan per-item
            $table->text('notes')->nullable(); // catatan per-item
            $table->timestamps();

            // Foreign keys
            $table->foreign('asset_request_id')->references('id')->on('assets_requests')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('master_assets_category')->onDelete('restrict');
            $table->foreign('location_id')->references('id')->on('master_assets_locations')->onDelete('restrict');
            $table->foreign('sub_location_id')->references('id')->on('master_assets_sub_locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_request_items');
    }
};
