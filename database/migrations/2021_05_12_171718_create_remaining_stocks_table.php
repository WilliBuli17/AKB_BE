<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemainingStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remaining_stocks', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_bahan')->unsigned();
            $table->foreign('id_bahan')
            ->references('id')->on('bahans')
            ->onDelete('CASCADE');
            
            $table->integer('jumlah_stock');
            $table->string('satuan_stock');
            $table->date('tanggal_stock');
            $table->boolean('is_Deleted')->default(0);
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
        Schema::dropIfExists('remaining_stocks');
    }
}
