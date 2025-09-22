<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3 fw-bold"><?= isset($user) ? 'Edit Pengguna: ' . esc($user['username']) : 'Tambah Pengguna Baru' ?></h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php $actionUrl = isset($user) ? 'users/update/' . esc($user['id']) : 'users/save'; ?>
                        <?= form_open($actionUrl); ?>
                            <?= csrf_field(); ?>

                            <div class="mb-3">
                                <label for="username" class="form-label">Nama Pengguna</label>
                                <input type="text" class="form-control form-control-md" id="username" name="username" value="<?= isset($user) ? esc($user['username']) : '' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password <?= isset($user) ? '(Kosongkan jika tidak ingin diubah)' : '' ?></label>
                                <input type="password" class="form-control form-control-md" id="password" name="password" <?= !isset($user) ? 'required' : '' ?>>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Ulangi Password</label>
                                <input class="form-control form-control-md" type="password" name="password_confirm" placeholder="Ulangi password baru" />
                            </div>

                            <div class="mb-3">
                                <label for="role_id" class="form-label">Pilih Peran (Role)</label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">Pilih...</option>
                                    <option value="1" <?= isset($user) && $user['role_id'] == 1 ? 'selected' : '' ?>>Admin</option>
                                    <option value="2" <?= isset($user) && $user['role_id'] == 2 ? 'selected' : '' ?>>Pengguna Biasa</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary"><?= isset($user) ? 'Perbarui' : 'Simpan' ?></button>
                            <a href="<?= base_url('users') ?>" class="btn btn-secondary">Batal</a>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>