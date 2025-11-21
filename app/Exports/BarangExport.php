<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangExport implements FromCollection, WithHeadings
{

    protected ?string $search;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = Barang::query();

        if ($this->search) {
            $s = strtolower($this->search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(nama_barang) LIKE ?', ['%' . $s . '%'])
                  ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . $s . '%'])
                  ->orWhereRaw('LOWER(COALESCE(lokasi_rak, \'\')) LIKE ?', ['%' . $s . '%']);
            });
        }

        return $query
            ->orderBy('nama_barang')
            ->get([
                'id',
                'nama_barang',
                'sku',
                'stok',
                'stok_minimum',
                'lokasi_rak',
                'created_at',
                'updated_at',
            ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Barang',
            'SKU',
            'Stok',
            'Stok Minimum',
            'Lokasi Rak',
            'Dibuat',
            'Diupdate',
        ];
    }
}
