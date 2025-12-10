<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            return;
        }

        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nippam')->nullable();
            $table->string('name');
            $table->uuid('departments_id')->nullable();
            $table->uuid('sub_department_id')->nullable();
            $table->uuid('employee_position_id')->nullable();
            $table->string('place_birth')->nullable();
            $table->date('date_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('religion')->nullable();
            $table->integer('age')->nullable();
            $table->text('address')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('id_number')->nullable();
            $table->string('email')->nullable();
            $table->uuid('users_id')->nullable();
            $table->timestamps();

            $table->foreign('departments_id')->references('id')->on('master_departments')->onDelete('set null');
            $table->foreign('sub_department_id')->references('id')->on('master_sub_departments')->onDelete('set null');
            $table->foreign('employee_position_id')->references('id')->on('master_employee_position')->onDelete('set null');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
