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
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->string('assets_number');
            $table->string('name');
            $table->uuid('category_id');
            $table->date('purchase_date');
            $table->uuid('condition_id');
            $table->uuid('transaction_status_id')->nullable();
            $table->string('img')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('funding_source');
            $table->string('brand');
            $table->string('book_value');
            $table->string('desc')->nullable();
            $table->date('book_value_expiry');
            $table->uuid('status_id');
            $table->timestamps();

            $table->uuid('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_status_id')->references('id')->on('master_assets_transaction_status');
            $table->foreign('category_id')->references('id')->on('master_assets_category');
            $table->foreign('condition_id')->references('id')->on('master_assets_condition');
            $table->foreign('status_id')->references('id')->on('master_assets_status');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
