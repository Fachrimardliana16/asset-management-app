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
        Schema::create('master_branch_office_units', function (Blueprint $table) {
            $table->uuid('id')->primary;
            $table->uuid('branch_unit_id');
            $table->uuid('branch_office_id');
            $table->timestamps();

            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_unit_id')->references('id')->on('master_branch_unit')->onDelete('cascade');
            $table->foreign('branch_office_id')->references('id')->on('master_branch_office')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_branch_office_units');
    }
};
