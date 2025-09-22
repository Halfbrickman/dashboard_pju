<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-3 fw-bold">Daftar Pengguna</h1>
            <?php if (session()->get('role_id') == 1) : ?>
                <a href="<?= base_url('users/create') ?>" class="btn btn-primary btn-md fw-bold">Tambah Pengguna</a>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <div class="table-responsive">
                            <table class="table table-hover my-0">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Pengguna</th>
                                        <th>Role</th>
                                        <?php if (session()->get('role_id') == 1) : ?>
                                            <th class="text-center">Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)) : ?>
                                        <?php $no = 1; ?>
                                        <?php foreach ($users as $user) : ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= esc($user['username']); ?></td>
                                                <td><?= esc($user['nama_roles']); ?></td>
                                                <?php if (session()->get('role_id') == 1) : ?>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('users/edit/' . esc($user['id'])) ?>" class="btn btn-warning btn-sm">Edit</a>
                                                        <a href="#" class="btn btn-danger btn-sm delete-btn" data-id="<?= esc($user['id']) ?>">Hapus</a>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada data pengguna yang ditemukan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // SweetAlert untuk notifikasi sukses
    <?php if (session()->getFlashdata('success')) : ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success'); ?>',
            showConfirmButton: false,
            timer: 2000
        });
    <?php endif; ?>

    // SweetAlert untuk konfirmasi hapus
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-id');
            const deleteUrl = '<?= base_url('users/delete/') ?>' + userId;

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        });
    });
</script>