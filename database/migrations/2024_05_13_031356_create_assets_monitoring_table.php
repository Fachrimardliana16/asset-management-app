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
        Schema::create('assets_monitoring', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('monitoring_date');
            $table->uuid('assets_id');
            $table->string('assets_number');
            $table->string('name');
            $table->uuid('old_condition_id');
            $table->uuid('new_condition_id');
            $table->uuid('user_id')->nullable();
            $table->text('desc')->nullable();
            $table->timestamps();

            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assets_id')->references('id')->on('assets');
            $table->foreign('old_condition_id')->references('id')->on('master_assets_condition');
            $table->foreign('new_condition_id')->references('id')->on('master_assets_condition');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_monitoring');
    }
};
