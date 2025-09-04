<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex">
            <h1 class="h3 mb-3 fw-bold">Master Data Judul Keterangan</h1>
            <?php if (session()->get('role_id') == 1) : ?>
                <div class="ms-auto card-tools">
                    <a href="/judul-keterangan/form" class="btn btn-primary btn-md fw-bold">Tambah Data</a>
                </div>
            <?php endif; ?>
        </div>

        <?php foreach ($groupedJudulKeterangan as $sumberData => $rows) : ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title fw-bold">Sumber Data: <?= esc($sumberData); ?></h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover my-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Keterangan</th>
                                        <?php if (session()->get('role_id') == 1) : ?>
                                            <th class="text-center">Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($rows as $row) : ?>
                                        <tr>
                                            <td><?= esc($i++); ?></td>
                                            <td><?= esc($row['jdl_keterangan']); ?></td>
                                            <?php if (session()->get('role_id') == 1) : ?>
                                                <td class="text-end" style="width: 150px;">
                                                    <a href="/judul-keterangan/form/<?= esc($row['id_jdlketerangan']); ?>" class="btn btn-warning btn-sm" style="border-radius: 10px;">Edit</a>
                                                    <form action="/judul-keterangan/delete/<?= esc($row['id_jdlketerangan']); ?>" method="post" class="d-inline delete-form">
                                                        <?= csrf_field(); ?>
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 10px;">Hapus</button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>