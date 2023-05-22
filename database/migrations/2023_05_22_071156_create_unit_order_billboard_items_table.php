<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitOrderBillboardItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_order_billboard_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billboard_unit_id');
            $table->unsignedBigInteger('unit_order_id');
            $table->integer('quantity')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unit_order_billboard_items');
    }
}
