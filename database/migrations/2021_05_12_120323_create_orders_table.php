<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('total_menu_order')->default(0);
            $table->integer('total_item_order')->default(0);
            $table->double('total_harga_order')->default(0);
            $table->date('tanggal_order');
            $table->boolean('status_order')->default(0);

            $table->bigInteger('id_reservasi')->unsigned();
            $table->foreign('id_reservasi')
            ->references('id')->on('reservasis')
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
        Schema::dropIfExists('orders');
    }
}
