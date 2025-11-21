<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\TransaksiBarang;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;


class TransaksiBarangApiController extends Controller
{
    public function index(Request $request)
    {
        $query = TransaksiBarang::with(['barang:id,nama_barang,sku,lokasi_rak']);

        // Filter by jenis: MASUK / KELUAR
        if ($request->filled('jenis')) {
            $jenis = strtoupper($request->input('jenis'));
            if (in_array($jenis, ['MASUK', 'KELUAR'])) {
                $query->where('jenis', $jenis);
            }
        }

        // Filter by tanggal (range)
        if ($request->filled('tanggal_from')) {
            $query->whereDate('tanggal', '>=', $request->input('tanggal_from'));
        }

        if ($request->filled('tanggal_to')) {
            $query->whereDate('tanggal', '<=', $request->input('tanggal_to'));
        }

        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->input('barang_id'));
        }

        $allowedSorts = ['tanggal', 'qty', 'jenis'];
        $sortBy = $request->input('sort_by', 'tanggal');
        $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'tanggal';
        }

        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = $perPage > 0 ? $perPage : 15;

        $paginated = $query->paginate($perPage);

        // ringkasan (summary) berdasarkan data yang sama dengan query (tanpa pagination)
        $summaryQuery = clone $query;
        $allForSummary = $summaryQuery->get();

        $total = $allForSummary->count();
        $totalQtyMasuk = $allForSummary->where('jenis', 'MASUK')->sum('qty');
        $totalQtyKeluar = $allForSummary->where('jenis', 'KELUAR')->sum('qty');
        $countMasuk = $allForSummary->where('jenis', 'MASUK')->count();
        $countKeluar = $allForSummary->where('jenis', 'KELUAR')->count();

        $summary = [
            'total_transaksi'       => $total,
            'total_qty_masuk'       => $totalQtyMasuk,
            'total_qty_keluar'      => $totalQtyKeluar,
            'jumlah_transaksi_masuk'  => $countMasuk,
            'jumlah_transaksi_keluar' => $countKeluar,
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi barang',
            'data'    => $paginated->items(),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
                'summary'      => $summary,
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id'  => 'required|integer|exists:barangs,id',
            'jenis'      => 'required|in:MASUK,KELUAR',
            'qty'        => 'required|integer|min:1',
            'tanggal'    => 'nullable|date',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Pastikan $barang SELALU didefinisikan di awal
        $barang = Barang::find($validated['barang_id']);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'error'   => 'Barang tidak ditemukan',
            ], 404);
        }

        return DB::transaction(function () use ($validated, $barang) {
            // Cegah stok minus untuk transaksi KELUAR
            if ($validated['jenis'] === 'KELUAR') {
                if ($barang->stok < $validated['qty']) {
                    return response()->json([
                        'success' => false,
                        'error'   => 'Stok tidak mencukupi untuk transaksi keluar',
                    ], 422);
                }
                $barang->decrement('stok', $validated['qty']);
            } else {
                // MASUK
                $barang->increment('stok', $validated['qty']);
            }

            $transaksi = TransaksiBarang::create([
                'barang_id'  => $barang->id,
                'jenis'      => $validated['jenis'],
                'qty'        => $validated['qty'],
                'tanggal'    => $validated['tanggal'] ?? now(),
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Activity Log aman, $barang ada di scope use()
            ActivityLog::record(
                module: 'transaksi',
                action: 'CREATE',
                description: "Transaksi {$validated['jenis']} barang ID {$barang->id} ({$barang->nama_barang}), qty {$validated['qty']}",
                data: [
                    'transaksi_id' => $transaksi->id,
                    'barang_id'    => $barang->id,
                    'jenis'        => $validated['jenis'],
                    'qty'          => $validated['qty'],
                    'tanggal'      => $transaksi->tanggal,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'data'    => $transaksi,
            ], 201);
        });
    }

}
