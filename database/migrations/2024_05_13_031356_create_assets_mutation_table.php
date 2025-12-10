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
        Schema::create('assets_mutation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('mutations_number')->unique();
            $table->date('mutation_date');
            $table->uuid('assets_id');
            $table->string('assets_number');
            $table->string('name');
            $table->uuid('condition_id');
            $table->uuid('employees_id');
            $table->uuid('location_id');
            $table->uuid('sub_location_id');
            $table->uuid('transaction_status_id');
            $table->string('scan_doc')->nullable();
            $table->text('desc')->nullable();
            $table->timestamps();

            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assets_id')->references('id')->on('assets');
            $table->foreign('employees_id')->references('id')->on('employees');
            $table->foreign('location_id')->references('id')->on('master_assets_locations');
            $table->foreign('sub_location_id')->references('id')->on('master_assets_sub_locations');
            $table->foreign('transaction_status_id')->references('id')->on('master_assets_transaction_status');
            $table->foreign('condition_id')->references('id')->on('master_assets_condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_mutation');
    }
};
