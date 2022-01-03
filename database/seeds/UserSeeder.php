<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')
            ->insert([
                'nama_pegawai' => 'Admin404',
                'gender_pegawai' => 'null',
                'telepon_pegawai' => 'null',
                'jabatan_pegawai' => 'admin',
                'tanggal_bergabung_pegawai' => '2021-01-01',
                'foto_pegawai' => 'null',
                'email' => 'admin404@gmail.com',
                'password' => '$2b$10$HJME7sH9dxfKtUwafEDKSOpMWmBX6fcKJWmorLTJ7VrdVYF4xaRBK',
                'status_pegawai' => '1',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now()

            ]);
    }
}