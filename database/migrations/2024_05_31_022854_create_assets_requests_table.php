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
        Schema::create('assets_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_number');
            $table->date('date')->default(now());
            $table->string('asset_name');
            $table->uuid('category_id');
            $table->integer('quantity');
            $table->string('purpose');
            $table->text('desc')->nullable();
            $table->boolean('status_request')->default(false);
            $table->boolean('kepala_sub_bagian')->default(false);
            $table->boolean('kepala_bagian_umum')->default(false);
            $table->boolean('kepala_bagian_keuangan')->default(false);
            $table->boolean('direktur_umum')->default(false);
            $table->boolean('direktur_utama')->default(false);
            $table->string('docs');
            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('master_assets_category');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_requests');
    }
};
