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
        Schema::create('master_sub_departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('departments_id');
            $table->string('name');
            $table->timestamps();
            $table->foreign('departments_id')->references('id')->on('master_departments')->onDelete('cascade');

            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_sub_departments');
    }
};
