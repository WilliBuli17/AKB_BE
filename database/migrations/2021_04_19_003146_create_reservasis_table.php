<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservasisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_reservasi');
            $table->string('sesi_reservasi');
            $table->boolean('status_reservasi')->default(0);
            $table->boolean('is_Deleted')->default(0);

            $table->bigInteger('id_customer')->unsigned();
            $table->foreign('id_customer')
            ->references('id')->on('customers')
            ->onDelete('CASCADE');

            $table->bigInteger('id_table')->unsigned();
            $table->foreign('id_table')
            ->references('id')->on('tables')
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
        Schema::dropIfExists('reservasis');
    }
}
