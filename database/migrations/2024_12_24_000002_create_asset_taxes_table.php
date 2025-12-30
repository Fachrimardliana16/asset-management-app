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
        Schema::create('asset_taxes', function (Blueprint $table) {
            $table->id();
            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreignId('tax_type_id')->constrained('master_tax_types')->onDelete('restrict');
            
            // Data Pajak
            $table->year('tax_year');
            $table->decimal('tax_amount', 15, 2);
            $table->date('due_date'); // Tanggal jatuh tempo
            $table->date('payment_date')->nullable(); // Tanggal pembayaran
            
            // Status
            $table->enum('payment_status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Denda
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->text('penalty_calculation')->nullable()->comment('Detail kalkulasi denda');
            $table->integer('overdue_days')->default(0)->comment('Jumlah hari keterlambatan');
            
            // Bukti dan Catatan
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // User tracking
            $table->uuid('paid_by')->nullable();
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
            $table->uuid('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['asset_id', 'tax_year']);
            $table->index('payment_status');
            $table->index('approval_status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_taxes');
    }
};
