<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('assetrequest_id');
            $table->string('document_number');
            $table->string('assets_number');
            $table->string('asset_name');
            $table->uuid('category_id');
            $table->date('purchase_date');
            $table->uuid('condition_id');
            $table->string('img')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('funding_source');
            $table->string('brand');
            $table->uuid('users_id');
            $table->timestamps();

            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assetrequest_id')->references('id')->on('assets_requests');
            $table->foreign('category_id')->references('id')->on('master_assets_category');
            $table->foreign('condition_id')->references('id')->on('master_assets_condition');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_purchases');
    }
}
