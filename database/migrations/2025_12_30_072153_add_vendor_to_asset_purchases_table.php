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
            $table->string('vendor')->nullable()->after('funding_source');
            $table->string('purchase_location')->nullable()->after('vendor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_purchases', function (Blueprint $table) {
            $table->dropColumn(['vendor', 'purchase_location']);
        });
    }
};
