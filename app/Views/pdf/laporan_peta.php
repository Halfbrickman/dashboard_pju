<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Peta</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dddddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Data Peta Koordinat</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Sumber Data</th>
                <th>Kota/Kab</th>
                <th>Kecamatan</th>
                <th>Kelurahan</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <?php foreach ($dynamicHeaders as $header) : ?>
                    <th><?= esc($header); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($processedData)): ?>
                <?php $no = 1; foreach ($processedData as $item): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= esc($item['nama_sumber']); ?></td>
                        <td><?= esc($item['nama_kotakab']); ?></td>
                        <td><?= esc($item['nama_kec']); ?></td>
                        <td><?= esc($item['nama_kel']); ?></td>
                        <td><?= esc($item['latitude']); ?></td>
                        <td><?= esc($item['longitude']); ?></td>
                        <?php foreach ($dynamicHeaders as $header) : ?>
                            <td><?= esc($item[$header]); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= 7 + count($dynamicHeaders); ?>" style="text-align: center;">
                        Tidak ada data untuk ditampilkan.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>