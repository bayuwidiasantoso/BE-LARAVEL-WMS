<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Boleh kosong (hanya header), atau isi 1 baris contoh
        return new Collection([
            [
                'nama_barang'  => 'Kardus 20x20',
                'sku'          => 'KD-20',
                'stok'         => 50,
                'stok_minimum' => 10,
                'lokasi_rak'   => 'A-01',
            ],
        ]);
    }

    public function headings(): array
    {
        // HARUS sama dengan yang dibaca BarangImport
        return [
            'nama_barang',
            'sku',
            'stok',
            'stok_minimum',
            'lokasi_rak',
        ];
    }
}
