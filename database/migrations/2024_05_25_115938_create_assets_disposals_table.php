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
        Schema::create('assets_disposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('disposal_date');
            $table->string('disposals_number')->unique();
            $table->uuid('assets_id');
            $table->decimal('book_value', 15, 2);
            $table->text('disposal_reason');
            $table->decimal('disposal_value', 15, 2);
            $table->text('disposal_process');
            $table->uuid('employee_id');
            $table->text('disposal_notes')->nullable();
            $table->string('docs')->nullable();
            $table->timestamps();

            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');

            $table->foreign('assets_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_disposals');
    }
};
