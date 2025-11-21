<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogApiController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->orderByDesc('created_at');

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('data', 'like', '%' . $search . '%');
            });
        }

        $perPage = max((int) $request->input('per_page', 20), 1);

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Activity logs',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    public function autocomplete(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if ($q === '') {
            return response()->json([
                'success' => true,
                'message' => 'Daftar barang kosong (query tidak diberikan)',
                'data'    => [],
            ]);
        }

        $limit = (int) $request->input('limit', 10);

        $items = Barang::query()
            ->where(function ($qb) use ($q) {
                $qb->where('nama_barang', 'ilike', '%' . $q . '%')
                ->orWhere('sku', 'ilike', '%' . $q . '%');
            })
            ->orderBy('nama_barang')
            ->limit($limit)
            ->get(['id', 'nama_barang', 'sku', 'stok', 'lokasi_rak']);

        return response()->json([
            'success' => true,
            'message' => 'Hasil autocomplete barang',
            'data'    => $items,
        ]);
    }
}
