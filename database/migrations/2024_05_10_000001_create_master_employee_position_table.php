<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_employee_position')) {
            return;
        }

        Schema::create('master_employee_position', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('desc')->nullable();
            $table->uuid('users_id')->nullable();
            $table->timestamps();

            $table->foreign('users_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_employee_position');
    }
};
