<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang</title>
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
    <h1>Laporan Daftar Barang</h1>
    <div class="meta">
        Dicetak: {{ now()->format('d-m-Y H:i') }}<br>
        Total: {{ $barangs->count() }} barang
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>SKU</th>
                <th>Stok</th>
                <th>Stok Min</th>
                <th>Lokasi Rak</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($barangs as $b)
                <tr>
                    <td>{{ $b->nama_barang }}</td>
                    <td>{{ $b->sku }}</td>
                    <td>{{ $b->stok }}</td>
                    <td>{{ $b->stok_minimum }}</td>
                    <td>{{ $b->lokasi_rak }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
