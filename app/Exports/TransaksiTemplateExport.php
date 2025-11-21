<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransaksiTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Satu baris contoh (opsional, bisa dikosongkan kalau mau)
        return new Collection([
            [
                'sku'        => 'KD-20',
                'jenis'      => 'MASUK',
                'qty'        => 10,
                'tanggal'    => '2025-01-15 10:00:00',
                'keterangan' => 'Restock awal',
            ],
        ]);
    }

    public function headings(): array
    {
        // Sesuai dengan TransaksiImport: sku | jenis | qty | tanggal | keterangan
        return [
            'sku',
            'jenis',
            'qty',
            'tanggal',
            'keterangan',
        ];
    }
}
