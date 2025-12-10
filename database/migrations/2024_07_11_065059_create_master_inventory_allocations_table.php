<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterInventoryAllocationsTable extends Migration
{
    public function up()
    {
        Schema::create('master_inventory_allocations', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('breakdown')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_inventory_allocations');
    }
}
