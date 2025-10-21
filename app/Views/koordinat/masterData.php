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

                        <form action="/koordinat" method="get" class="form-inline mb-4 d-flex align-items-center flex-wrap">

                            <div class="form-group mr-2 me-2 mb-2">
                                <select name="sumberdata" id="sumberdata" class="form-select form-select-sm">
                                    <option value="">Filter Berdasarkan Sumber Data</option>
                                    <?php foreach ($sumberdata as $sd) : ?>
                                        <option value="<?= esc($sd['id_sumberdata']); ?>" <?= (isset($selectedSumberdata) && $selectedSumberdata == $sd['id_sumberdata']) ? 'selected' : '' ?>>
                                            <?= esc($sd['nama_sumber']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mr-2 me-2 mb-2">
                                <input type="text" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Cari..." value="<?= esc($keyword ?? '') ?>">
                            </div>

                            <input type="hidden" name="per_page" id="hidden_per_page" value="<?= esc($perPage ?? 10) ?>">

                            <button type="submit" class="btn btn-primary btn-sm mb-2">Terapkan Filter & Cari</button>

                            <?php if ($selectedSumberdata || $keyword) : ?>
                                <a href="/koordinat" class="btn btn-secondary btn-sm ms-2 mb-2">Reset</a>
                            <?php endif; ?>

                        </form>
                        <form id="delete-multiple-form" action="/koordinat/deleteMultiple" method="post" class="mb-4">
                            <?= csrf_field(); ?>
                            <?php if (session()->get('role_id') == 1) : ?>
                                <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="form-group d-flex align-items-center">
                                        <?php
                                        $perPageOptions = [10, 25, 50, 100];
                                        $currentPerPage = $perPage ?? 10;
                                        ?>
                                        <label for="per_page_moved" class="me-2 mb-0 text-nowrap">Tampilkan data per halaman:</label>
                                        <select name="per_page_moved" id="per_page_moved" class="form-select form-select-sm" onchange="submitPerPageForm(this.value)">
                                            <?php foreach ($perPageOptions as $option) : ?>
                                                <option value="<?= $option; ?>" <?= ($currentPerPage == $option) ? 'selected' : '' ?>>
                                                    <?= $option; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ms-auto">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteMultiple()">Hapus Terpilih</button>
                                    </div>
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
                                            // 🔑 LOGIKA PENOMORAN URUT DENGAN PAGER
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
                            </div>
                        </form>
                        </div>

                    <div class="card-footer">
                        <?php
                        // Persiapkan query string agar filter dan per_page tetap ada saat berganti halaman
                        $querystring = [
                            'sumberdata' => $selectedSumberdata,
                            'keyword' => $keyword,
                            'per_page' => $perPage,
                        ];
                        // Menghapus entri yang bernilai null atau kosong
                        $querystring = array_filter($querystring);

                        // Tampilkan links dengan membawa semua parameter filter
                        echo $pager->links('default', 'bootstrap_pagination', $querystring);
                        ?>
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

    // Kode untuk Hapus Massal
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
            text: "Data yang dipilih akan dihapus!",
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

    function submitPerPageForm(perPageValue) {

        document.getElementById('hidden_per_page').value = perPageValue;

        document.querySelector('form[action="/koordinat"]').submit();
    }
</script>