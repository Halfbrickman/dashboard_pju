<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><?= esc($title); ?></h1>

        <div class="row">
            <div class="col-12 col-lg-8">
                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="alert alert-success" role="alert">
                        <?= session()->getFlashdata('success'); ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= session()->getFlashdata('error'); ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('failed_rows')) : ?>
                    <div class="alert alert-warning" role="alert">
                        <strong>Detail Kegagalan:</strong>
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('failed_rows') as $failure) : ?>
                                <li><?= esc($failure); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Unggah File Excel</h5>
                        <h6 class="card-subtitle text-muted">Unggah file Excel (.xlsx) untuk menambahkan data koordinat secara massal.</h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('koordinat/upload'); ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field(); ?>
                            <div class="mb-3">
                                <label for="excel_file" class="form-label">Pilih File Excel (.xlsx)</label>
                                <input class="form-control" type="file" id="excel_file" name="excel_file" required accept=".xlsx">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Unggah dan Proses
                            </button>
                            <a href="<?= base_url('asset/template/template_excel.xlsx'); ?>" class="btn btn-info" download>
                                <i class="fas fa-download"></i> Unduh Template
                            </a>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Unggah Foto</h5>
                        <h6 class="card-subtitle text-muted">Unggah file foto (.jpg, .png, dll) yang terkait dengan data yang diimpor.</h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('koordinat/uploadPhotos'); ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field(); ?>
                            <div class="mb-3">
                                <label for="photos" class="form-label">Pilih Foto (Multi-select)</label>
                                <input class="form-control" type="file" id="photos" name="photos[]" multiple accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label for="zip_file" class="form-label">Atau Unggah File ZIP</label>
                                <input class="form-control" type="file" id="zip_file" name="zip_file" accept=".zip">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Unggah Foto
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>