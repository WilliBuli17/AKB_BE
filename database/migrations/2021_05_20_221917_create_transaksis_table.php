<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->double('subtotal_harga')->default(0);
            $table->double('biaya_pajak')->default(0);
            $table->double('biaya_service')->default(0);
            $table->double('total_harga')->default(0);
            $table->string('tipe_pembayaran');
            $table->date('tanggal_transaksi');
            $table->integer('nomor_resi');
            $table->string('kode_resi');
            $table->string('kode_verivikasi_edc')->nullable();

            $table->bigInteger('id_kartu')->nullable()->unsigned();
            $table->foreign('id_kartu')
            ->references('id')->on('kartus')
            ->onDelete('CASCADE');

            $table->bigInteger('id_order')->unsigned();
            $table->foreign('id_order')
            ->references('id')->on('orders')
            ->onDelete('CASCADE');

            $table->bigInteger('id_pegawai')->unsigned();
            $table->foreign('id_pegawai')
            ->references('id')->on('users')
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
        Schema::dropIfExists('transaksis');
    }
}
