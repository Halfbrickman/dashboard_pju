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
                        <?php if (session()->getFlashdata('success')) : ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('success'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

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
                                                        <a href="<?= base_url('users/delete/' . esc($user['id'])) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">Hapus</a>
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