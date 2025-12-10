<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryDpbTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_dpb', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('dpb_number');
            $table->date('date');
            $table->string('for');
            $table->string('status');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_dpb');
    }
}
