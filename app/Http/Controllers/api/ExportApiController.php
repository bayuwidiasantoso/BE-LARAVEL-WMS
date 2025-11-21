<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\BarangExport;
use App\Exports\TransaksiExport;
use App\Models\Barang;
use App\Models\TransaksiBarang;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportApiController extends Controller
{
    public function barangExcel(Request $request)
    {
        $search = $request->input('search');

        return Excel::download(new BarangExport($search), 'barang.xlsx');
    }

    public function barangPdf(Request $request)
    {
        $search = $request->input('search');

        $query = Barang::query();

        if ($search) {
            $s = strtolower($search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(nama_barang) LIKE ?', ['%' . $s . '%'])
                  ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . $s . '%'])
                  ->orWhereRaw('LOWER(COALESCE(lokasi_rak, \'\')) LIKE ?', ['%' . $s . '%']);
            });
        }

        $barangs = $query->orderBy('nama_barang')->get();

        $pdf = Pdf::loadView('exports.barang', compact('barangs'));

        return $pdf->download('barang.pdf');
    }

    public function transaksiExcel(Request $request)
    {
        $filters = $request->only([
            'jenis',
            'tanggal_from',
            'tanggal_to',
            'barang_id',
            'sort_by',
            'sort_dir',
        ]);

        return Excel::download(new TransaksiExport($filters), 'transaksi.xlsx');
    }

    public function transaksiPdf(Request $request)
    {
        $filters = $request->only([
            'jenis',
            'tanggal_from',
            'tanggal_to',
            'barang_id',
            'sort_by',
            'sort_dir',
        ]);

        $query = TransaksiBarang::with('barang:id,nama_barang,sku');

        if (!empty($filters['jenis'])) {
            $jenis = strtoupper($filters['jenis']);
            if (in_array($jenis, ['MASUK', 'KELUAR'])) {
                $query->where('jenis', $jenis);
            }
        }

        if (!empty($filters['tanggal_from'])) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_from']);
        }

        if (!empty($filters['tanggal_to'])) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_to']);
        }

        if (!empty($filters['barang_id'])) {
            $query->where('barang_id', $filters['barang_id']);
        }

        $sortBy  = $filters['sort_by']  ?? 'tanggal';
        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal', 'qty', 'jenis'];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'tanggal';
        }

        $transaksis = $query->orderBy($sortBy, $sortDir)->get();

        $pdf = Pdf::loadView('exports.transaksi', compact('transaksis'));

        return $pdf->download('transaksi.pdf');
    }
}
