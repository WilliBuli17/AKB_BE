<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_menu');
            $table->string('tipe_menu');
            $table->text('deskripsi_menu');
            $table->string('foto_menu');
            $table->string('satuan_menu');
            $table->double('harga_menu');
            $table->integer('jumlah_menu_tersedia')->default(0);
            $table->boolean('is_Deleted')->default(0);

            $table->bigInteger('id_bahan')->unsigned();
            $table->foreign('id_bahan')
            ->references('id')->on('bahans')
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
        Schema::dropIfExists('menus');
    }
}
