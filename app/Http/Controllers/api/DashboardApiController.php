<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\TransaksiBarang;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function summary()
    {
        $totalBarang = Barang::count();
        $totalStok = Barang::sum('stok');

        $today = Carbon::today();

        $transaksiHariIni = TransaksiBarang::whereDate('tanggal', $today)->get();

        $totalMasukHariIni = $transaksiHariIni->where('jenis', 'MASUK')->sum('qty');
        $totalKeluarHariIni = $transaksiHariIni->where('jenis', 'KELUAR')->sum('qty');

        // top 5 barang paling sering transaksi (berdasarkan total qty +/-)
        $topBarang = TransaksiBarang::selectRaw('barang_id, SUM(qty) as total_qty')
            ->groupBy('barang_id')
            ->orderByDesc('total_qty')
            ->with('barang:id,nama_barang,sku,lokasi_rak')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'barang_id'   => $row->barang_id,
                    'nama_barang' => $row->barang?->nama_barang,
                    'sku'         => $row->barang?->sku,
                    'total_qty'   => $row->total_qty,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan dashboard WMS',
            'data'    => [
                'total_barang'           => $totalBarang,
                'total_stok'             => $totalStok,
                'total_masuk_hari_ini'   => $totalMasukHariIni,
                'total_keluar_hari_ini'  => $totalKeluarHariIni,
                'top_barang'             => $topBarang,
            ],
        ], 200);
    }

    // Tambahan untuk grafik time series
    public function timeSeries(Request $request)
    {
        $days = (int) $request->input('days', 7);
        if ($days < 1) $days = 7;
        if ($days > 60) $days = 60;

        $end   = Carbon::today();
        $start = (clone $end)->subDays($days - 1);

        $rows = TransaksiBarang::selectRaw('DATE(tanggal) as tgl, jenis, SUM(qty) as total_qty')
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->groupBy('tgl', 'jenis')
            ->orderBy('tgl')
            ->get();

        $period = new \DatePeriod($start, new \DateInterval('P1D'), (clone $end)->addDay());

        $labels = [];
        $masuk  = [];
        $keluar = [];

        foreach ($period as $date) {
            $tgl = $date->format('Y-m-d');
            $labels[] = $tgl;

            $masukRow = $rows->firstWhere(fn ($r) => $r->tgl === $tgl && $r->jenis === 'MASUK');
            $keluarRow = $rows->firstWhere(fn ($r) => $r->tgl === $tgl && $r->jenis === 'KELUAR');

            $masuk[]  = $masukRow ? (int) $masukRow->total_qty : 0;
            $keluar[] = $keluarRow ? (int) $keluarRow->total_qty : 0;
        }

        return response()->json([
            'success' => true,
            'message' => 'Time series transaksi per hari',
            'data'    => [
                'labels' => $labels,
                'masuk'  => $masuk,
                'keluar' => $keluar,
            ],
        ], 200);
    }
}
