<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3 fw-bold"><?= $title; ?></h1>

        <div class="row">
            <div class="col-12">
                <form action="/judul-keterangan/saveOrUpdate" method="post">
                    <?= csrf_field(); ?>
                    <?php if ($judulKeterangan) : ?>
                        <input type="hidden" name="id_jdlketerangan" value="<?= esc($judulKeterangan['id_jdlketerangan']); ?>">
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Judul Keterangan</h5>
                        </div>
                        <div class="card-body">
                            <input type="text" name="jdl_keterangan" class="form-control <?= (session('errors.jdl_keterangan')) ? 'is-invalid' : ''; ?>" placeholder="Judul Keterangan" value="<?= old('jdl_keterangan', $judulKeterangan['jdl_keterangan'] ?? ''); ?>">
                            <?php if (session('errors.jdl_keterangan')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.jdl_keterangan'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-header">
                            <h5 class="card-title mb-0">Sumber Data</h5>
                        </div>
                        <div class="card-body">
                            <select name="id_sumberdata" class="form-select <?= (session('errors.id_sumberdata')) ? 'is-invalid' : ''; ?>">
                                <option value="">-- Pilih Sumber Data --</option>
                                <?php foreach ($sumberdata as $sumber) : ?>
                                    <option value="<?= esc($sumber['id_sumberdata']); ?>" <?= (old('id_sumberdata', $judulKeterangan['id_sumberdata'] ?? '') == $sumber['id_sumberdata']) ? 'selected' : ''; ?>>
                                        <?= esc($sumber['nama_sumber']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('errors.id_sumberdata')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.id_sumberdata'); ?>
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