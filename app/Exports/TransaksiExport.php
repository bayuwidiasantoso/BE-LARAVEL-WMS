<?php

namespace App\Exports;

use App\Models\TransaksiBarang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransaksiExport implements FromCollection, WithHeadings
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = TransaksiBarang::with('barang:id,nama_barang,sku');

        if (!empty($this->filters['jenis'])) {
            $jenis = strtoupper($this->filters['jenis']);
            if (in_array($jenis, ['MASUK', 'KELUAR'])) {
                $query->where('jenis', $jenis);
            }
        }

        if (!empty($this->filters['tanggal_from'])) {
            $query->whereDate('tanggal', '>=', $this->filters['tanggal_from']);
        }

        if (!empty($this->filters['tanggal_to'])) {
            $query->whereDate('tanggal', '<=', $this->filters['tanggal_to']);
        }

        if (!empty($this->filters['barang_id'])) {
            $query->where('barang_id', $this->filters['barang_id']);
        }

        $sortBy  = $this->filters['sort_by']  ?? 'tanggal';
        $sortDir = strtolower($this->filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal', 'qty', 'jenis'];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'tanggal';
        }

        return $query
            ->orderBy($sortBy, $sortDir)
            ->get()
            ->map(function ($t) {
                return [
                    'tanggal'     => $t->tanggal,
                    'jenis'       => $t->jenis,
                    'qty'         => $t->qty,
                    'barang_id'   => $t->barang_id,
                    'nama_barang' => $t->barang?->nama_barang,
                    'sku'         => $t->barang?->sku,
                    'keterangan'  => $t->keterangan,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Jenis',
            'Qty',
            'Barang ID',
            'Nama Barang',
            'SKU',
            'Keterangan',
        ];
    }
}
