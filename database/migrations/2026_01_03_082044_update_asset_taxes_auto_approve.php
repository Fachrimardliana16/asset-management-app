<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update semua record existing yang pending menjadi approved
        DB::table('asset_taxes')
            ->where('approval_status', 'pending')
            ->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada rollback - data sudah di-approve
    }
};
