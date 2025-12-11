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
        Schema::create('master_branch_unit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('desc')->nullable();

            // KOLOM BARU UNTUK KODE AKUNTANSI
            $table->string('accounting_code', 5)->unique();

            $table->timestamps();

            // KOLOM BARU UNTUK MAPPING KE CABANG
            $table->uuid('branch_office_id')->nullable();
            $table->foreign('branch_office_id')->references('id')->on('master_branch_office')->onDelete('set null');

            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_branch_unit');
    }
};
