<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Barang</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #444; padding: 4px 6px; }
        th { background: #eee; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .meta { font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <h1>Laporan Transaksi Barang</h1>
    <div class="meta">
        Dicetak: {{ now()->format('d-m-Y H:i') }}<br>
        Total: {{ $transaksis->count() }} transaksi
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Qty</th>
                <th>Nama Barang</th>
                <th>SKU</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksis as $t)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('d-m-Y H:i') }}</td>
                    <td>{{ $t->jenis }}</td>
                    <td>{{ $t->qty }}</td>
                    <td>{{ $t->barang?->nama_barang }}</td>
                    <td>{{ $t->barang?->sku }}</td>
                    <td>{{ $t->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
