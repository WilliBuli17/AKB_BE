<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Bahan extends Model
{
    protected $fillable = [
        'nama_bahan', 'jenis_bahan', 'jumlah_bahan', 'total_berat_bersih', 
        'ukuran_porsi', 'satuan_bahan', 'is_Deleted'
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
