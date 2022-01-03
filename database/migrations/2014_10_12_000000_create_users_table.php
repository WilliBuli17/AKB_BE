<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pegawai');
            $table->string('gender_pegawai');
            $table->string('telepon_pegawai');
            $table->string('jabatan_pegawai');
            $table->date('tanggal_bergabung_pegawai');
            $table->string('foto_pegawai');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('status_pegawai')->default(1);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
