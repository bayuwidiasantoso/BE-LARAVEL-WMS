<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\TransaksiBarang;
use App\Models\ActivityLog; // ⬅️ TAMBAHKAN IMPORT INI

class BarangApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        // 🔍 SEARCH (opsional)
        if ($search = $request->input('search')) {
            $search = strtolower($search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nama_barang) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(COALESCE(lokasi_rak, \'\')) LIKE ?', ['%' . $search . '%']);
            });
        }

        $allowedSorts = [
            'id',
            'nama_barang',
            'sku',
            'stok',
            'lokasi_rak',
            'stok_minimum',
        ];

        $sortBy  = $request->input('sort_by', 'nama_barang');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'nama_barang';
        }

        $query->orderBy($sortBy, $sortDir);

        $barangs = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar barang',
            'data'    => $barangs,
        ], 200);
    }

    public function show($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail barang',
            'data'    => $barang,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang'  => 'required|string|max:255',
            'sku'          => 'required|string|max:100|unique:barangs,sku',
            'stok'         => 'required|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'lokasi_rak'   => 'nullable|string|max:100',
        ]);

        if (!isset($validated['stok_minimum'])) {
            $validated['stok_minimum'] = 0;
        }

        $barang = Barang::create($validated);

        ActivityLog::record(
            module: 'barang',
            action: 'CREATE',
            description: "Tambah barang: {$barang->nama_barang} ({$barang->sku})",
            data: $barang->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dibuat',
            'data'    => $barang,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'nama_barang'  => 'required|string|max:255',
            'sku'          => 'required|string|max:100|unique:barangs,sku,' . $barang->id,
            'stok'         => 'required|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'lokasi_rak'   => 'nullable|string|max:100',
        ]);

        if (!isset($validated['stok_minimum'])) {
            $validated['stok_minimum'] = 0;
        }

        $barang->update($validated);

        ActivityLog::record(
            module: 'barang',
            action: 'UPDATE',
            description: "Update barang: {$barang->nama_barang} ({$barang->sku})",
            data: $barang->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diupdate',
            'data'    => $barang,
        ], 200);
    }

    public function destroy($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        // ⬅️ SIMPAN COPY SEBELUM DELETE
        $copy = $barang->toArray();

        $barang->delete();

        ActivityLog::record(
            module: 'barang',
            action: 'DELETE',
            description: "Hapus barang ID {$copy['id']} ({$copy['nama_barang']})",
            data: $copy
        );

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus',
        ], 200);
    }

    public function detailWithHistory($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        $riwayat = TransaksiBarang::where('barang_id', $barang->id)
            ->orderByDesc('tanggal')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Detail barang + riwayat transaksi',
            'data'    => [
                'barang'  => $barang,
                'history' => $riwayat,
            ],
        ], 200);
    }

    public function autocomplete(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([
                'success' => true,
                'message' => 'Query kosong, tidak ada hasil',
                'data'    => [],
            ]);
        }

        $limit = (int) $request->query('limit', 10);
        if ($limit <= 0) {
            $limit = 10;
        }

        $items = Barang::query()
            ->where(function ($qb) use ($q) {
                // kalau pakai PostgreSQL, pakai ILIKE
                $qb->where('nama_barang', 'ILIKE', '%' . $q . '%')
                   ->orWhere('sku', 'ILIKE', '%' . $q . '%');
            })
            ->orderBy('nama_barang')
            ->limit($limit)
            ->get([
                'id',
                'nama_barang',
                'sku',
                'stok',
                'lokasi_rak',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil autocomplete barang',
            'data'    => $items,
        ]);
    }

    public function lowStock(Request $request)
    {
        $threshold = (int) $request->input('threshold', 10);
        if ($threshold < 0) {
            $threshold = 0;
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0) {
            $perPage = 10;
        }

        $page = (int) $request->input('page', 1);
        if ($page <= 0) {
            $page = 1;
        }

        $queryMinimum = Barang::where('stok_minimum', '>', 0)
            ->whereColumn('stok', '<', 'stok_minimum');

        $useMinimum = $queryMinimum->exists();

        if ($useMinimum) {
            $baseQuery     = $queryMinimum;
            $mode          = 'stok_minimum';
            $usedThreshold = null;
        } else {
            $baseQuery     = Barang::where('stok', '<', $threshold);
            $mode          = 'threshold';
            $usedThreshold = $threshold;
        }

        $baseQuery
            ->orderBy('stok', 'asc')
            ->orderBy('nama_barang', 'asc');

        $paginator = $baseQuery->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success'   => true,
            'message'   => $mode === 'stok_minimum'
                ? 'Daftar barang dengan stok di bawah stok_minimum'
                : 'Daftar barang dengan stok di bawah threshold ' . $usedThreshold,
            'data'      => $paginator->items(),
            'meta'      => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'mode'         => $mode,
                'threshold'    => $usedThreshold,
            ],
            'mode'      => $mode,
            'threshold' => $usedThreshold,
        ], 200);
    }
}
