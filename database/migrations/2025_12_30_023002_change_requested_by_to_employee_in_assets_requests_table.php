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
        // Cek apakah foreign key exists sebelum di-drop
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'assets_requests' 
            AND CONSTRAINT_NAME = 'assets_requests_requested_by_foreign'
        ");
        
        if (!empty($foreignKeys)) {
            Schema::table('assets_requests', function (Blueprint $table) {
                // Hapus foreign key lama ke users
                $table->dropForeign(['requested_by']);
            });
        }
        
        // Set semua requested_by yang tidak valid ke null
        DB::statement('UPDATE assets_requests SET requested_by = NULL WHERE requested_by IS NOT NULL AND requested_by NOT IN (SELECT id FROM employees)');
        
        Schema::table('assets_requests', function (Blueprint $table) {
            // Ubah foreign key ke employees
            $table->foreign('requested_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets_requests', function (Blueprint $table) {
            // Kembalikan ke users
            $table->dropForeign(['requested_by']);
        });
        
        // Set semua requested_by yang tidak valid ke null
        DB::statement('UPDATE assets_requests SET requested_by = NULL WHERE requested_by IS NOT NULL AND requested_by NOT IN (SELECT id FROM users)');
        
        Schema::table('assets_requests', function (Blueprint $table) {
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
