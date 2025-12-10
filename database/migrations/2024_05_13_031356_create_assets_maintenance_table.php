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
        Schema::create('assets_maintenance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('maintenance_date');
            $table->string('location_service');
            $table->uuid('assets_id');
            $table->string('service_type');
            $table->decimal('service_cost', 10, 2);
            $table->text('desc')->nullable();
            $table->string('invoice_file')->nullable();
            $table->timestamps();

            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assets_id')->references('id')->on('assets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_maintenance');
    }
};
