<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3"><?= esc($title); ?></h1>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><?= esc($title); ?></h3>
                    </div>
                    <div class="card-body">
                        <?= session()->getFlashdata('errors') ? \Config\Services::validation()->listErrors() : '' ?>

                        <form action="/koordinat/save" method="post">
                            <?= csrf_field(); ?>
                            <?php if (isset($koordinat)) : ?>
                                <input type="hidden" name="id_koordinat" value="<?= esc($koordinat['id_koordinat']); ?>">
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="latitude">Lattitude</label>
                                <input type="text" class="form-control" id="latitude" name="latitude" value="<?= old('latitude', isset($koordinat) ? $koordinat['latitude'] : ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="longitude">Longitude</label>
                                <input type="text" class="form-control" id="longitude" name="longitude" value="<?= old('longitude', isset($koordinat) ? $koordinat['longitude'] : ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="id_kotakab">Kota/Kab</label>
                                <select name="id_kotakab" id="id_kotakab" class="form-control">
                                    <option value="">-- Pilih Kota/Kab --</option>
                                    <?php foreach ($kotakab as $row) : ?>
                                        <option value="<?= esc($row['id_kotakab']); ?>" <?= old('id_kotakab', isset($koordinat) ? $koordinat['id_kotakab'] : '') == $row['id_kotakab'] ? 'selected' : '' ?>>
                                            <?= esc($row['nama_kotakab']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_kec">Kecamatan</label>
                                <select name="id_kec" id="id_kec" class="form-control" disabled>
                                    <option value="">-- Pilih Kecamatan --</option>
                                    <?php foreach ($kecamatan as $row) : ?>
                                        <option value="<?= esc($row['id_kec']); ?>" <?= old('id_kec', isset($koordinat) ? $koordinat['id_kec'] : '') == $row['id_kec'] ? 'selected' : '' ?>>
                                            <?= esc($row['nama_kec']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_kel">Kelurahan</label>
                                <select name="id_kel" id="id_kel" class="form-control" disabled>
                                    <option value="">-- Pilih Kelurahan --</option>
                                    <?php foreach ($kelurahan as $row) : ?>
                                        <option value="<?= esc($row['id_kel']); ?>" <?= old('id_kel', isset($koordinat) ? $koordinat['id_kel'] : '') == $row['id_kel'] ? 'selected' : '' ?>>
                                            <?= esc($row['nama_kel']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_sumberdata">Sumber Data</label>
                                <select name="id_sumberdata" id="id_sumberdata" class="form-control" required>
                                    <option value="">-- Pilih Sumber Data --</option>
                                    <?php foreach ($sumberdata as $row) : ?>
                                        <option value="<?= esc($row['id_sumberdata']); ?>" <?= old('id_sumberdata', isset($koordinat) ? $koordinat['id_sumberdata'] : '') == $row['id_sumberdata'] ? 'selected' : '' ?>>
                                            <?= esc($row['nama_sumber']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="keterangan-fields"></div>
                            
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="/koordinat" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const baseUrl = "<?= base_url(); ?>";
    const editMode = <?= isset($koordinat) ? 'true' : 'false' ?>;

    $(document).ready(function() {
        // Function to load keterangan fields
        function loadKeteranganFields(idSumberData, isiKeterangan = []) {
            const keteranganContainer = $('#keterangan-fields');
            keteranganContainer.empty();

            if (idSumberData) {
                $.ajax({
                    url: baseUrl + 'api/judul-keterangan/' + idSumberData,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                let keteranganValue = '';
                                const foundKeterangan = isiKeterangan.find(item => item.id_jdlketerangan == value.id_jdlketerangan);
                                if (foundKeterangan) {
                                    keteranganValue = foundKeterangan.isi_keterangan;
                                }

                                const formHtml = `
                                    <div class="form-group">
                                        <label for="keterangan-${value.id_jdlketerangan}">
                                            ${value.jdl_keterangan}
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="keterangan-${value.id_jdlketerangan}" 
                                               name="keterangan[${value.id_jdlketerangan}]"
                                               value="${keteranganValue}"
                                               >
                                    </div>`;
                                keteranganContainer.append(formHtml);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Gagal mengambil data judul keterangan:", error);
                    }
                });
            }
        }
        
        // Initial load for edit mode
        if (editMode) {
            const initialSumberDataId = $('#id_sumberdata').val();
            const initialIsiKeterangan = <?= json_encode($isiKeterangan); ?>;
            if (initialSumberDataId) {
                loadKeteranganFields(initialSumberDataId, initialIsiKeterangan);
            }
        }

        // Change event for Sumber Data dropdown
        $('#id_sumberdata').change(function() {
            const idSumberData = $(this).val();
            loadKeteranganFields(idSumberData);
        });

        // Other change events (Kota/Kab, Kecamatan)
        $('#id_kotakab').change(function() {
            const id_kotakab = $(this).val();
            if (id_kotakab) {
                $.ajax({
                    url: baseUrl + 'api/kecamatan/' + id_kotakab,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#id_kec').empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', false);
                        $('#id_kel').empty().append('<option value="">-- Pilih Kelurahan --</option>').prop('disabled', true);
                        $.each(data, function(key, value) {
                            $('#id_kec').append('<option value="' + value.id_kec + '">' + value.nama_kec + '</option>');
                        });
                    }
                });
            } else {
                $('#id_kec').empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
                $('#id_kel').empty().append('<option value="">-- Pilih Kelurahan --</option>').prop('disabled', true);
            }
        });

        $('#id_kec').change(function() {
            const id_kec = $(this).val();
            if (id_kec) {
                $.ajax({
                    url: baseUrl + 'api/kelurahan/' + id_kec,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#id_kel').empty().append('<option value="">-- Pilih Kelurahan --</option>').prop('disabled', false);
                        $.each(data, function(key, value) {
                            $('#id_kel').append('<option value="' + value.id_kel + '">' + value.nama_kel + '</option>');
                        });
                    }
                });
            } else {
                $('#id_kel').empty().append('<option value="">-- Pilih Kelurahan --</option>').prop('disabled', true);
            }
        });
    });
</script>