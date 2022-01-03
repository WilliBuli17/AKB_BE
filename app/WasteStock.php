<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WasteStock extends Model
{
    protected $fillable = [
        'id_bahan', 'jumlah_stock', 'satuan_stock',
        'tanggal_stock', 'is_Deleted'
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
