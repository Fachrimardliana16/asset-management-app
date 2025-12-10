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
            $table->enum('purchase_status', ['pending', 'in_progress', 'purchased', 'cancelled'])
                ->default('pending')
                ->after('status_request')
                ->comment('Status pembelian: pending=belum diproses, in_progress=sedang diproses, purchased=sudah dibeli, cancelled=dibatalkan');
            $table->date('purchase_date')->nullable()->after('purchase_status');
            $table->text('purchase_notes')->nullable()->after('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets_requests', function (Blueprint $table) {
            $table->dropColumn(['purchase_status', 'purchase_date', 'purchase_notes']);
        });
    }
};
