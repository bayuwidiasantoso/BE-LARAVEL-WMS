<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BarangImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;
    public int $failedCount  = 0;

    /**
     * Header yang diharapkan:
     * nama_barang | sku | stok | stok_minimum | lokasi_rak
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Lewatkan kalau tidak ada SKU / nama
            if (empty($row['sku']) && empty($row['nama_barang'])) {
                $this->failedCount++;
                continue;
            }

            try {
                Barang::updateOrCreate(
                    [ 'sku' => $row['sku'] ],
                    [
                        'nama_barang'  => $row['nama_barang'] ?? '-',
                        'stok'         => isset($row['stok']) ? (int) $row['stok'] : 0,
                        'stok_minimum' => isset($row['stok_minimum']) ? (int) $row['stok_minimum'] : 0,
                        'lokasi_rak'   => $row['lokasi_rak'] ?? null,
                    ]
                );

                $this->successCount++;
            } catch (\Throwable $e) {
                $this->failedCount++;
            }
        }
    }
}
