<main class="content">
<div class="container-fluid p-0">
    <h1 class="h3 mb-3 fw-bold"><?= isset($user) ? 'Edit Pengguna: ' . esc($user['username']) : 'Tambah Pengguna Baru' ?></h1>

    <div class="row justify-content-center"> <div class="col-lg-12 col-md-8">
            <div class="card">
                <div class="card-body">
                    <?php $actionUrl = isset($user) ? 'users/update/' . esc($user['id']) : 'users/save'; ?>
                    
                    <?= form_open($actionUrl); ?>
                        <?= csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= old('nama', isset($user) ? esc($user['nama']) : '') ?>" required>
                            <?php if(session('errors.nama')): ?>
                                <small class="text-danger"><?= session('errors.nama') ?></small>
                            <?php endif ?>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= old('username', isset($user) ? esc($user['username']) : '') ?>" required>
                            <?php if(session('errors.username')): ?>
                                <small class="text-danger"><?= session('errors.username') ?></small>
                            <?php endif ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <?= isset($user) ? '(Kosongkan jika tidak ingin diubah)' : '' ?></label>
                            <input type="password" class="form-control" id="password" name="password" <?= !isset($user) ? 'required' : '' ?>>
                            <?php if(session('errors.password')): ?>
                                <small class="text-danger"><?= session('errors.password') ?></small>
                            <?php endif ?>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Ulangi Password</label>
                            <input class="form-control" type="password" name="password_confirm" placeholder="Ulangi password baru" />
                            <?php if(session('errors.password_confirm')): ?>
                                <small class="text-danger"><?= session('errors.password_confirm') ?></small>
                            <?php endif ?>
                        </div>

                        <div class="mb-3">
                            <label for="role_id" class="form-label">Pilih Peran (Role)</label>
                            
                            <?php if (isset($user) && $user['role_id'] == 1): ?>
                                <!-- 
                                JIKA SUPERADMIN (ID 1) SAAT EDIT:
                                Role tidak dapat diubah, kirim nilai '1' secara hidden.
                                -->
                                <input type="hidden" name="role_id" value="1">
                                <input type="text" class="form-control-plaintext" value="Superadmin (Tidak dapat diubah)" readonly>
                            <?php else: ?>
                                
                                <?php 
                                    $is_editing = isset($user); 
                                    // $disabled_attribute dihilangkan karena default-nya sekarang adalah TIDAK disabled (bisa diubah)
                                    $role_value = old('role_id', $is_editing ? $user['role_id'] : '');
                                ?>

                                <!-- 
                                Dropdown ditampilkan untuk TAMBAH BARU dan EDIT pengguna NON-SUPERADMIN.
                                Karena role_id BISA diubah, kita menggunakan name="role_id" langsung pada select.
                                -->
                                <select class="form-select" id="role_id" name="role_id" 
                                    <?= !$is_editing ? 'required' : '' ?>> <!-- 'required' hanya untuk TAMBAH baru -->
                                    
                                    <option value="">Pilih...</option>
                                    <?php if (!empty($roles)): ?>
                                        <?php foreach ($roles as $role): ?>
                                            <?php 
                                                // Logika untuk menyembunyikan opsi Superadmin (ID 1) dari dropdown biasa
                                                $skip_option = ($role['id'] == 1);
                                            ?>
                                            <?php if (!$skip_option): // Hanya tampilkan opsi jika BUKAN Superadmin (ID 1) ?>
                                                <option value="<?= esc($role['id']) ?>"
                                                    <?= $role_value == $role['id'] ? 'selected' : '' ?>>
                                                    <?= esc($role['nama_roles']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                
                                <?php if ($is_editing): ?>
                                    <small class="form-text text-muted">Anda dapat mengubah peran pengguna ini.</small>
                                <?php endif; ?>
                                
                            <?php endif; ?>

                            <?php if(session('errors.role_id')): ?>
                                <!-- Menampilkan error validasi role_id -->
                                <small class="text-danger"><?= session('errors.role_id') ?></small>
                            <?php endif ?>
                        </div>
                        
                        <div class="mb-4">
                            <label for="id_sumberdata" class="form-label">Sumber Data (Opsional/Global)</label>
                            <select class="form-select" id="id_sumberdata" name="id_sumberdata">
                                <option value="">Global (Akses Semua Data)</option>
                                <?php if (!empty($sumber_data)): ?>
                                    <?php foreach ($sumber_data as $sumber): ?>
                                        <option value="<?= esc($sumber['id_sumberdata']) ?>"
                                            <?= old('id_sumberdata', isset($user) ? $user['id_sumberdata'] : '') == $sumber['id_sumberdata'] ? 'selected' : '' ?>>
                                            <?= esc($sumber['nama_sumber']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Kosongkan untuk memberikan akses ke semua data (Global).</small>
                            <?php if(session('errors.id_sumberdata')): ?>
                                <small class="text-danger"><?= session('errors.id_sumberdata') ?></small>
                            <?php endif ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2"> <button type="submit" class="btn btn-primary"><?= isset($user) ? 'Perbarui' : 'Simpan' ?></button>
                            <a href="<?= base_url('users') ?>" class="btn btn-secondary">Batal</a>
                        </div>

                    <?= form_close(); ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>
</main>