<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaksi extends Model
{
    protected $fillable = [
        'subtotal_harga', 'biaya_pajak', 'biaya_service', 'total_harga',
        'tipe_pembayaran', 'tanggal_transaksi', 'nomor_resi', 'kode_resi', 
        'kode_verivikasi_edc', 'id_kartu', 'id_order', 'id_pegawai'
    ];

    public function getCreatedAtAttribute()
    {
        if (!is_null($this->attributes['created_at'])) {
            return Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i:s');
        }
    }

    public function getUpdatedAtAttribute()
    {
        if (!is_null($this->attributes['updated_at'])) {
            return Carbon::parse($this->attributes['updated_at'])->format('Y-m-d H:i:s');
        }
    }
}
