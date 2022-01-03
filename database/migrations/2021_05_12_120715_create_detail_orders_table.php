<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('jumlah_item_order');
            $table->double('harga_item_order');
            $table->boolean('status_item_order')->default(0);
            $table->boolean('is_Deleted')->default(0);

            $table->bigInteger('id_order')->unsigned();
            $table->foreign('id_order')
            ->references('id')->on('orders')
            ->onDelete('CASCADE');

            $table->bigInteger('id_menu')->unsigned();
            $table->foreign('id_menu')
            ->references('id')->on('menus')
            ->onDelete('CASCADE');
            
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
        Schema::dropIfExists('detail_orders');
    }
}
