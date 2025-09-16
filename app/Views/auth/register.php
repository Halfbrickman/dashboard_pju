<div class="container d-flex flex-column">
    <div class="row vh-100">
        <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
            <div class="d-table-cell align-middle">
                <div class="text-center mt-4">
                    <h1 class="h2">Buat Akun Baru</h1>
                    <p class="lead">Daftar untuk mulai menggunakan aplikasi.</p>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="m-sm-4">
                            <?php if (session()->getFlashdata('error')) : ?>
                                <div class="alert alert-danger"><?= session()->getFlashdata('error'); ?></div>
                            <?php endif; ?>
                            <form action="<?= base_url('auth/processRegister'); ?>" method="post">
                                <?= csrf_field(); ?>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input class="form-control form-control-lg" type="text" name="username" placeholder="Masukkan username" required />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input class="form-control form-control-lg" type="password" name="password" placeholder="Masukkan password" required />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ulangi Password</label>
                                    <input class="form-control form-control-lg" type="password" name="password_confirm" placeholder="Ulangi password" required />
                                </div>
                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-lg btn-primary">Daftar</button>
                                </div>
                            </form>
                            <div class="text-center mt-3">
                                <a href="<?= base_url('login'); ?>">Sudah punya akun? Login di sini.</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>