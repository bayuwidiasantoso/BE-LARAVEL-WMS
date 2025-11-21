<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\BarangImport;
use App\Imports\TransaksiImport;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BarangTemplateExport;
use App\Exports\TransaksiTemplateExport;

class ImportApiController extends Controller
{
    public function importBarang(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new BarangImport();
        Excel::import($import, $request->file('file'));

        ActivityLog::record(
            module: 'barang',
            action: 'IMPORT_EXCEL',
            description: "Import Barang: {$import->successCount} berhasil, {$import->failedCount} gagal",
            data: [
                'success' => $import->successCount,
                'failed'  => $import->failedCount,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Import Barang selesai. Berhasil: {$import->successCount}, Gagal: {$import->failedCount}",
            'data'    => [
                'success' => $import->successCount,
                'failed'  => $import->failedCount,
            ],
        ]);
    }

    public function importTransaksi(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new TransaksiImport();
        Excel::import($import, $request->file('file'));

        ActivityLog::record(
            module: 'transaksi',
            action: 'IMPORT_EXCEL',
            description: "Import Transaksi: {$import->successCount} berhasil, {$import->failedCount} gagal",
            data: [
                'success' => $import->successCount,
                'failed'  => $import->failedCount,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Import Transaksi selesai. Berhasil: {$import->successCount}, Gagal: {$import->failedCount}",
            'data'    => [
                'success' => $import->successCount,
                'failed'  => $import->failedCount,
            ],
        ]);
    }


    public function templateBarang()
    {
        return Excel::download(
            new BarangTemplateExport(),
            'template_import_barang.xlsx'
        );
    }

    public function templateTransaksi()
    {
        return Excel::download(
            new TransaksiTemplateExport(),
            'template_import_transaksi.xlsx'
        );
    }
}
