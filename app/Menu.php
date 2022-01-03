<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Menu extends Model
{   
    protected $fillable = [
        'nama_menu', 'tipe_menu', 'deskripsi_menu', 'foto_menu',
        'jumlah_menu_tersedia', 'harga_menu', 'satuan_menu',
        'status_menu', 'is_Deleted', 'id_bahan'
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
