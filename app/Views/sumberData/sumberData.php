<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-3 fw-bold">Master Sumber Data</h1>
            <div class="ms-auto card-tools">
                <?php if (session()->get('role_id') == 1) : ?>
                    <div class="ms-auto card-tools">
                        <a href="/sumberdata/form" class="btn btn-primary btn-md fw-bold">Tambah Data</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-hover my-0">
                            <thead>
                                <tr>
                                    <th>No.</th> <th>Nama Sumber Data</th>
                                    <th>Warna Marker</th>
                                    <?php if (session()->get('role_id') == 1) : ?>
                                        <th class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($sumber_data)) : ?>
                                    <?php $no = 1; ?> <?php foreach ($sumber_data as $sumber) : ?>
                                        <tr>
                                            <td><?= $no++; ?></td> <td><?= esc($sumber['nama_sumber']); ?></td>
                                            <td style="display: flex; gap: 10px; align-items: center;">
                                                <div style="width: 30px; height: 30px; background-color: <?= esc($sumber['warna']); ?>; border-radius: 25%;"></div>
                                                <div><?= esc($sumber['warna']); ?></div>
                                            </td>
                                            <?php if (session()->get('role_id') == 1) : ?>
                                                <td class="text-end" style="width: 150px;">
                                                    <a href="/sumberdata/form/<?= esc($sumber['id_sumberdata']); ?>" class="btn btn-warning btn-sm" style="border-radius: 10px;">Edit</a>
                                                    <form id="delete-form-<?= esc($sumber['id_sumberdata']); ?>" action="/sumberdata/delete/<?= esc($sumber['id_sumberdata']); ?>" method="post" class="d-inline">
                                                        <?= csrf_field(); ?>
                                                        <button type="button" class="btn btn-danger btn-sm delete-btn" style="border-radius: 10px;" data-id="<?= esc($sumber['id_sumberdata']); ?>" data-name="<?= esc($sumber['nama_sumber']); ?>">Hapus</button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= (session()->get('role_id') == 1) ? '4' : '3' ?>" class="text-center">Tidak ada data sumber ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php if (session()->getFlashdata('pesan')) : ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('pesan'); ?>',
            showConfirmButton: false,
            timer: 2000
        });
    </script>
<?php endif; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Data sumber "${name}" akan dihapus. Anda tidak akan bisa mengembalikannya!`,
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
            });
        });
    });
</script>