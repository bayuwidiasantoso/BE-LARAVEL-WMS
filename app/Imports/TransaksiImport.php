<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\TransaksiBarang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TransaksiImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;
    public int $failedCount  = 0;

    /**
     * Header yang diharapkan (salah satu sku / barang_sku):
     * sku | barang_sku | jenis | qty | tanggal | keterangan
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $sku = $row['sku'] ?? $row['barang_sku'] ?? null;

            if (!$sku || empty($row['jenis']) || empty($row['qty'])) {
                $this->failedCount++;
                continue;
            }

            $barang = Barang::where('sku', $sku)->first();
            if (!$barang) {
                $this->failedCount++;
                continue;
            }

            $jenis = strtoupper(trim($row['jenis']));
            if (!in_array($jenis, ['MASUK', 'KELUAR'])) {
                $this->failedCount++;
                continue;
            }

            $qty = (int) $row['qty'];
            if ($qty <= 0) {
                $this->failedCount++;
                continue;
            }

            // tanggal optional
            $tanggalStr = $row['tanggal'] ?? null;
            if ($tanggalStr) {
                try {
                    $tanggal = Carbon::parse($tanggalStr);
                } catch (\Throwable $e) {
                    $tanggal = now();
                }
            } else {
                $tanggal = now();
            }

            try {
                // insert transaksi
                TransaksiBarang::create([
                    'barang_id'  => $barang->id,
                    'jenis'      => $jenis,
                    'qty'        => $qty,
                    'tanggal'    => $tanggal,
                    'keterangan' => $row['keterangan'] ?? null,
                ]);

                // adjust stok sama dengan logic store()
                if ($jenis === 'MASUK') {
                    $barang->increment('stok', $qty);
                } else {
                    if ($barang->stok < $qty) {
                        // batalkan (jangan sampai minus)
                        $this->failedCount++;
                        continue;
                    }
                    $barang->decrement('stok', $qty);
                }

                $this->successCount++;
            } catch (\Throwable $e) {
                $this->failedCount++;
            }
        }
    }
}
