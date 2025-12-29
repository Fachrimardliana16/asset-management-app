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
        Schema::create('master_tax_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // PKB, BPKB, PBB, IMB, dll
            $table->string('code')->unique()->nullable(); // Kode pajak
            $table->text('description')->nullable();
            $table->uuid('asset_category_id')->nullable();
            $table->foreign('asset_category_id')->references('id')->on('master_assets_category')->onDelete('set null');
            
            // Periode pajak
            $table->enum('period_type', ['yearly', '5yearly', 'custom'])->default('yearly');
            $table->integer('period_months')->nullable()->comment('Untuk custom period dalam bulan');
            
            // Denda
            $table->boolean('has_penalty')->default(false);
            $table->decimal('penalty_percentage', 15, 2)->nullable()->comment('Persentase denda atau nominal tetap');
            $table->enum('penalty_type', ['percentage', 'fixed'])->default('percentage');
            $table->enum('penalty_period', ['daily', 'monthly'])->default('monthly')->comment('Perhitungan denda per hari atau per bulan');
            
            // Reminder
            $table->integer('reminder_days')->default(30)->comment('Berapa hari sebelum jatuh tempo kirim notifikasi');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tax_types');
    }
};
