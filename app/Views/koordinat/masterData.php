<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-3">Master Data</h1>
            <a href="/koordinat/form" class="btn btn-primary btn-sm ml-2">
                <i class="fas fa-plus"></i> Tambah Data
            </a>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/koordinat" method="get" class="form-inline mb-4">
                            <div class="form-group mr-2">
                                <label for="sumberdata" class="mr-2">Filter:</label>
                                <select name="sumberdata" id="sumberdata" class="form-control form-control-sm">
                                    <option value="">Semua Sumber Data</option>
                                    <?php foreach ($sumberdata as $sd) : ?>
                                        <option value="<?= esc($sd['id_sumberdata']); ?>" <?= ($selectedSumberdata == $sd['id_sumberdata']) ? 'selected' : '' ?>>
                                            <?= esc($sd['nama_sumber']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                        </form>
                        <table class="table table-hover my-0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Lattitude</th>
                                    <th>Longitude</th>
                                    <th>Kota/Kab</th>
                                    <th>Kecamatan</th>
                                    <th>Kelurahan</th>
                                    <th>Sumber Data</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($koordinat)) : ?>
                                    <?php
                                    $currentPage = $pager->getCurrentPage('default');
                                    $perPage = $pager->getPerPage('default');
                                    $i = ($currentPage - 1) * $perPage + 1;
                                    ?>
                                    <?php foreach ($koordinat as $row) : ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= esc($row['latitude']); ?></td>
                                            <td><?= esc($row['longitude']); ?></td>
                                            <td><?= esc($row['nama_kotakab']); ?></td>
                                            <td><?= esc($row['nama_kec']); ?></td>
                                            <td><?= esc($row['nama_kel']); ?></td>
                                            <td><?= esc($row['nama_sumber']); ?></td>
                                            <td>
                                                <a href="/koordinat/form/<?= esc($row['id_koordinat']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= esc($row['id_koordinat']); ?>)">Hapus</button>
                                                <form id="delete-form-<?= esc($row['id_koordinat']); ?>" action="/koordinat/delete/<?= esc($row['id_koordinat']); ?>" method="post" class="d-inline">
                                                    <?= csrf_field(); ?>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center">Tidak ada data koordinat ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <?= $pager->links('default', 'bootstrap_pagination'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if (session()->getFlashdata('success')) : ?>
        Swal.fire({
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success'); ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>