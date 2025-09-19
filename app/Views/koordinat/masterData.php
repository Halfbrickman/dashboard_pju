<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-3 fw-bold">Master Data Koordinat</h1>
            <?php if (session()->get('role_id') == 1) : ?>
                <div class="ms-auto card-tools">
                    <a href="/koordinat/form" class="btn btn-primary btn-md fw-bold">Tambah Data</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/koordinat" method="get" class="form-inline mb-4 d-flex align-items-center">
                            <div class="form-group mr-2 me-2">
                                <select name="sumberdata" id="sumberdata" class="form-select form-select-sm">
                                    <option value="">Filter Berdasarkan Sumber Data</option>
                                    <?php foreach ($sumberdata as $sd) : ?>
                                        <option value="<?= esc($sd['id_sumberdata']); ?>" <?= ($selectedSumberdata == $sd['id_sumberdata']) ? 'selected' : '' ?>>
                                            <?= esc($sd['nama_sumber']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group mr-2 me-2">
                                <input type="text" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Cari..." value="<?= esc($keyword ?? '') ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-sm">Terapkan Filter & Cari</button>
                            
                            <?php if ($selectedSumberdata || $keyword) : ?>
                                <a href="/koordinat" class="btn btn-secondary btn-sm ms-2">Reset</a>
                            <?php endif; ?>
                        </form>

                        <form id="delete-multiple-form" action="/koordinat/delete_multiple" method="post" class="mb-4">
                            <?= csrf_field(); ?>
                            <?php if (session()->get('role_id') == 1) : ?>
                                <div class="mb-3 d-flex justify-content-end">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteMultiple()">Hapus Terpilih</button>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
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
                                            <?php if (session()->get('role_id') == 1) : ?>
                                                <th>Aksi</th>
                                                <th><input type="checkbox" id="checkAll"></th>
                                            <?php endif; ?>
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
                                                    <?php if (session()->get('role_id') == 1) : ?>
                                                        <td>
                                                            <a href="/koordinat/form/<?= esc($row['id_koordinat']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= esc($row['id_koordinat']); ?>)">Hapus</button>
                                                            <form id="delete-form-<?= esc($row['id_koordinat']); ?>" action="/koordinat/delete/<?= esc($row['id_koordinat']); ?>" method="post" class="d-inline">
                                                                <?= csrf_field(); ?>
                                                            </form>
                                                        </td>
                                                        <td><input type="checkbox" name="selected[]" value="<?= esc($row['id_koordinat']); ?>" class="checkItem"></td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center">Tidak ada data koordinat ditemukan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div> </form>
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

    // Kode baru untuk Hapus Massal
    document.getElementById('checkAll').onclick = function() {
        var checkboxes = document.getElementsByName('selected[]');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    }

    function confirmDeleteMultiple() {
        var checkboxes = document.getElementsByName('selected[]');
        var anyChecked = false;
        for (var checkbox of checkboxes) {
            if (checkbox.checked) {
                anyChecked = true;
                break;
            }
        }

        if (!anyChecked) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih setidaknya satu data untuk dihapus.'
            });
            return;
        }

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dipilih akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-multiple-form').submit();
            }
        });
    }
</script>