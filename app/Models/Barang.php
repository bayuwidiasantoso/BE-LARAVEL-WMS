<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Barang extends Model
{
    //
    protected $fillable = [
        'nama_barang',
        'sku',
        'stok',
        'stok_minimum',
        'lokasi_rak',
    ];

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiBarang::class);
    }

}
