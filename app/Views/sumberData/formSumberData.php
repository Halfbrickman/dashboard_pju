<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3 fw-bold"><?= $title; ?></h1>

        <div class="row">
            <div class="col-12">
                <form action="/sumberdata/saveOrUpdate" method="post">
                    <?= csrf_field(); ?>
                    <?php if ($sumber) : ?>
                        <input type="hidden" name="id_sumberdata" value="<?= esc($sumber['id_sumberdata']); ?>">
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Nama Sumber Data</h5>
                        </div>
                        <div class="card-body">
                            <input type="text" name="nama_sumber" class="form-control <?= (session('errors.nama_sumber')) ? 'is-invalid' : ''; ?>" placeholder="Nama Sumber Data" value="<?= old('nama_sumber', $sumber['nama_sumber'] ?? ''); ?>">
                            <?php if (session('errors.nama_sumber')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.nama_sumber'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-header">
                            <h5 class="card-title mb-0">Warna Marker</h5>
                        </div>
                        <div class="" style="height: auto; padding-bottom: 1rem; padding-left: 1rem;">
                            <input type="color" name="warna" class="form-control form-control-color <?= (session('errors.warna')) ? 'is-invalid' : ''; ?>" id="exampleColorInput" value="<?= old('warna', $sumber['warna'] ?? '#563d7c'); ?>" title="Choose your color">
                            <?php if (session('errors.warna')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.warna'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary btn-lg fw-bold" style="margin: 1rem; border-radius: 10px; width: 15%;" type="submit">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</main>