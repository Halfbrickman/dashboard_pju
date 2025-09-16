<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">Peta Data</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select" id="filterSumberData">
                                    <option value="">Semua Sumber Data</option>
                                    <?php foreach ($sumber_data as $sumber) : ?>
                                        <option value="<?= $sumber['id_sumberdata']; ?>" data-color="<?= $sumber['warna']; ?>"><?= $sumber['nama_sumber']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6" id="filterWilayahDiv">
                                <div class="row">
                                    <div class="col-md-4">
                                        <select class="form-select" id="filterKota">
                                            <option value="">Semua Kota/Kab</option>
                                            <?php foreach ($kotakab as $kota) : ?>
                                                <option value="<?= $kota['id_kotakab']; ?>"><?= $kota['nama_kotakab']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="filterKecamatan" disabled>
                                            <option value="">Semua Kecamatan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="filterKelurahan" disabled>
                                            <option value="">Semua Kelurahan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end justify-content-end">
                                <div class="d-flex gap-2">
                                    <a href="<?= base_url('koordinat/import'); ?>" class="btn btn-primary" style="height: 40px;">
                                        <i class="fas fa-file-import"></i> Import
                                    </a>

                                    <div class="btn-group">
                                        <button type="button" style="height: 40px; " class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-file-export"></i> Export
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" id="exportKML" href="<?= base_url('map/exportKML'); ?>">Format KML</a></li>
                                            <li><a class="dropdown-item" id="exportExcel" href="<?= base_url('map/exportExcel'); ?>">Format Excel (.xlsx)</a></li>
                                            <li><a class="dropdown-item" id="exportPDF" href="<?= base_url('map/exportPDF'); ?>">Format PDF</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="mapid" style="height: 600px;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Data Marker</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit_id_koordinat">
                    <div class="mb-3">
                        <label for="edit_sumberdata" class="form-label">Sumber Data:</label>
                        <select id="edit_sumberdata" class="form-select" disabled></select>
                        <input type="hidden" id="edit_sumberdata_hidden" name="id_sumberdata">
                    </div>
                    <div class="mb-3">
                        <label for="edit_kotakab" class="form-label">Kota/Kab:</label>
                        <select id="edit_kotakab" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kecamatan" class="form-label">Kecamatan:</label>
                        <select id="edit_kecamatan" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kelurahan" class="form-label">Kelurahan:</label>
                        <select id="edit_kelurahan" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_latitude" class="form-label">Latitude:</label>
                        <input type="text" id="edit_latitude" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="edit_longitude" class="form-label">Longitude:</label>
                        <input type="text" id="edit_longitude" class="form-control">
                    </div>
                    <hr>
                    <h6>Keterangan Tambahan</h6>
                    <div id="additional-details-container"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveEditButton">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadPhotoModalLabel">Unggah Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadPhotoForm" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="koordinat_id" id="upload_koordinat_id">
                    <div class="mb-3">
                        <label for="photos" class="form-label">Pilih Foto (Bisa lebih dari satu):</label>
                        <input type="file" name="photos[]" id="photos" class="form-control" multiple>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="uploadPhotoForm" class="btn btn-primary">Unggah Foto</button>
            </div>
        </div>
    </div>
</div>

<script>
    const allJudulKeterangan = <?= json_encode($judul_keterangan); ?>;
    const isAdmin = <?= session()->get('role_id') == 1 ? 'true' : 'false'; ?>;
    let allMarkersData = [];
    let markerLayers = {};

    const allSumberData = <?= json_encode($sumber_data); ?>;
    const allKotaKab = <?= json_encode($kotakab); ?>;
    let allKecamatan = [];
    let allKelurahan = [];

    var map = L.map('mapid').setView([-6.2088, 106.8456], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    var markers = L.markerClusterGroup();

    function createCustomIcon(color) {
        var iconUrl = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="' + color + '" d="M128 252.6C128 148.4 214 64 320 64C426 64 512 148.4 512 252.6C512 371.9 391.8 514.9 341.6 569.4C329.8 582.2 310.1 582.2 298.3 569.4C248.1 514.9 127.9 371.9 127.9 252.6zM320 320C355.3 320 384 291.3 384 256C384 220.7 355.3 192 320 192C284.7 192 256 220.7 256 256C256 291.3 284.7 320 320 320z"/></svg>');
        return L.icon({
            iconUrl: iconUrl,
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });
    }

    function renderPopupContent(item) {
        let popupContent = `
            <strong>Sumber Data:</strong> ${item.nama_sumber || '-'}<br>
            <strong>Kota/Kab:</strong> ${item.nama_kotakab || '-'}<br>
            <strong>Kecamatan:</strong> ${item.nama_kec || '-'}<br>
            <strong>Kelurahan:</strong> ${item.nama_kel || '-'}<br>
            <strong>Latitude:</strong> ${item.latitude || '-'}<br>
            <strong>Longitude:</strong> ${item.longitude || '-'}<br>
            <hr>
        `;

        if (item.photos && item.photos.length > 0) {
            popupContent += `
                <h6>Foto-foto:</h6>
                <div class="photo-gallery" style="display: flex; flex-wrap: wrap; gap: 5px; max-height: 150px; overflow-y: auto;">
            `;
            item.photos.forEach(photo => {
                const photoUrl = `<?= base_url('/'); ?>${photo.file_path}`;
                popupContent += `
                    <a href="${photoUrl}" target="_blank">
                        <img src="${photoUrl}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; cursor: pointer;">
                    </a>
                `;
            });
            popupContent += `</div><hr>`;
        }
        
        if (isAdmin) {
            popupContent += `
                <button onclick="openEditModal('${item.id_koordinat}')" class="btn btn-warning btn-sm" style="border-radius: 10px; margin-right: 5px;">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete('${item.id_koordinat}')" style="border-radius: 10px;">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
                <button onclick="openUploadPhotoModal('${item.id_koordinat}')" class="btn btn-info btn-sm" style="border-radius: 10px; margin-top: 5px;">
                    <i class="fas fa-camera"></i> Unggah Foto
                </button>
                <br><br>
            `;
        }

        popupContent += `
            <a href="https://www.google.com/maps/search/?api=1&query=${item.latitude},${item.longitude}" target="_blank" class="btn btn-info btn-sm">Lihat di Google Maps</a>
        `;

        return popupContent;
    }

    function loadMarkers() {
        markers.clearLayers();
        map.removeLayer(markers);
        allMarkersData = [];
        markerLayers = {};

        const sumberId = document.getElementById('filterSumberData').value;
        const idKotakab = document.getElementById('filterKota').value;
        const idKec = document.getElementById('filterKecamatan').value;
        const idKel = document.getElementById('filterKelurahan').value;

        let url = `<?= base_url('api/markers'); ?>?sumber_data_id=${sumberId}`;
        if (idKotakab) url += `&id_kotakab=${idKotakab}`;
        if (idKec) url += `&id_kec=${idKec}`;
        if (idKel) url += `&id_kel=${idKel}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data yang diterima:', data);
                
                allMarkersData = data;
                allMarkersData.forEach(item => {
                    // Cek apakah latitude dan longitude ada dan valid
                    const lat = parseFloat(item.latitude);
                    const lng = parseFloat(item.longitude);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        var color = item.warna;
                        var customIcon = createCustomIcon(color);
                        var marker = L.marker([lat, lng], {
                            icon: customIcon
                        });

                        markerLayers[item.id_koordinat] = marker;
                        marker.bindPopup(renderPopupContent(item));
                        markers.addLayer(marker);
                    } else {
                        console.warn(`Data tidak valid untuk item dengan id_koordinat: ${item.id_koordinat}`);
                    }
                });
                map.addLayer(markers);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }

    function openEditModal(id) {
        const item = allMarkersData.find(d => d.id_koordinat == id);
        if (!item) {
            console.error('Data item tidak ditemukan untuk ID:', id);
            return;
        }

        document.getElementById('edit_id_koordinat').value = item.id_koordinat;
        document.getElementById('edit_latitude').value = item.latitude;
        document.getElementById('edit_longitude').value = item.longitude;

        const editSumberDataSelect = document.getElementById('edit_sumberdata');
        editSumberDataSelect.innerHTML = '<option value="">Pilih Sumber Data</option>';
        allSumberData.forEach(sumber => {
            const newOption = document.createElement('option');
            newOption.value = sumber.id_sumberdata;
            newOption.textContent = sumber.nama_sumber;
            if (sumber.id_sumberdata == item.id_sumberdata) {
                newOption.selected = true;
            }
            editSumberDataSelect.appendChild(newOption);
        });

        const editKotaKabSelect = document.getElementById('edit_kotakab');
        editKotaKabSelect.innerHTML = '<option value="">Pilih Kota/Kab</option>';
        allKotaKab.forEach(kotakab => {
            const newOption = document.createElement('option');
            newOption.value = kotakab.id_kotakab;
            newOption.textContent = kotakab.nama_kotakab;
            if (kotakab.id_kotakab == item.id_kotakab) {
                newOption.selected = true;
            }
            editKotaKabSelect.appendChild(newOption);
        });

        populateKecamatan(item.id_kotakab, item.id_kec);
        populateKelurahan(item.id_kec, item.id_kel);

        const additionalDetailsContainer = document.getElementById('additional-details-container');
        additionalDetailsContainer.innerHTML = '';

        const filteredJudul = allJudulKeterangan.filter(j => j.id_sumberdata == item.id_sumberdata);

        if (filteredJudul.length > 0) {
            filteredJudul.forEach(judul => {
                const detailItem = item.keterangan.find(k => k.jdl_keterangan === judul.jdl_keterangan);
                const value = detailItem ? detailItem.isi_keterangan : '';

                const detailHtml = `
                <div class="mb-3">
                    <label for="edit_keterangan_${judul.id_jdlketerangan}" class="form-label">${judul.jdl_keterangan}:</label>
                    <input type="text" id="edit_keterangan_${judul.id_jdlketerangan}" name="keterangan[${judul.id_jdlketerangan}]" class="form-control" value="${value}">
                </div>
            `;
                additionalDetailsContainer.innerHTML += detailHtml;
            });
        } else {
            additionalDetailsContainer.innerHTML = `<p class="text-muted">Tidak ada keterangan tambahan untuk sumber data ini.</p>`;
        }

        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }

    function populateKecamatan(selectedKotaKabId, selectedKecamatanId = null) {
        const editKecamatanSelect = document.getElementById('edit_kecamatan');
        editKecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
        editKecamatanSelect.disabled = !selectedKotaKabId;

        if (selectedKotaKabId) {
            fetch(`<?= base_url('api/kecamatan_by_kotakab'); ?>?id_kotakab=${selectedKotaKabId}`)
                .then(response => response.json())
                .then(data => {
                    allKecamatan = data; 
                    allKecamatan.forEach(kecamatan => {
                        const newOption = document.createElement('option');
                        newOption.value = kecamatan.id_kec;
                        newOption.textContent = kecamatan.nama_kec;
                        if (kecamatan.id_kec == selectedKecamatanId) {
                            newOption.selected = true;
                        }
                        editKecamatanSelect.appendChild(newOption);
                    });
                });
        }
    }

    function populateKelurahan(selectedKecamatanId, selectedKelurahanId = null) {
        const editKelurahanSelect = document.getElementById('edit_kelurahan');
        editKelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';
        editKelurahanSelect.disabled = !selectedKecamatanId;

        if (selectedKecamatanId) {
            fetch(`<?= base_url('api/kelurahan_by_kecamatan'); ?>?id_kec=${selectedKecamatanId}`)
                .then(response => response.json())
                .then(data => {
                    allKelurahan = data;
                    allKelurahan.forEach(kelurahan => {
                        const newOption = document.createElement('option');
                        newOption.value = kelurahan.id_kel;
                        newOption.textContent = kelurahan.nama_kel;
                        if (kelurahan.id_kel == selectedKelurahanId) {
                            newOption.selected = true;
                        }
                        editKelurahanSelect.appendChild(newOption);
                    });
                });
        }
    }

    document.getElementById('edit_kotakab').addEventListener('change', function() {
        populateKecamatan(this.value);
        populateKelurahan(null);
    });

    document.getElementById('edit_kecamatan').addEventListener('change', function() {
        populateKelurahan(this.value);
    });

    document.getElementById('saveEditButton').addEventListener('click', function() {
        const id = document.getElementById('edit_id_koordinat').value;
        const keteranganData = {};
        document.querySelectorAll('#additional-details-container input').forEach(input => {
            const idJdlKeterangan = input.name.match(/\[(\d+)\]/)[1];
            keteranganData[idJdlKeterangan] = input.value;
        });

        const updatedData = {
            id_koordinat: document.getElementById('edit_id_koordinat').value,
            id_sumberdata: document.getElementById('edit_sumberdata').value,
            id_kotakab: document.getElementById('edit_kotakab').value,
            id_kec: document.getElementById('edit_kecamatan').value,
            id_kel: document.getElementById('edit_kelurahan').value,
            latitude: document.getElementById('edit_latitude').value,
            longitude: document.getElementById('edit_longitude').value,
            keterangan: keteranganData
        };

        fetch(`<?= base_url('api/markers/update'); ?>`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify(updatedData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success');
                    var editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                    editModal.hide();
                    loadMarkers();
                } else {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan data.', 'error');
                }
            })
            .catch(error => {
                console.error('Error saving data:', error);
                Swal.fire('Gagal!', 'Terjadi kesalahan jaringan.', 'error');
            });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda tidak akan dapat mengembalikan ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('<?= base_url('koordinat/delete/'); ?>' + id, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.fire(
                            'Dihapus!',
                            'Data telah berhasil dihapus.',
                            'success'
                        ).then(() => {
                            loadMarkers();
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Gagal!',
                            'Terjadi kesalahan saat menghapus data.',
                            'error'
                        );
                    });
            }
        });
    }

    function openUploadPhotoModal(koordinatId) {
        const form = document.getElementById('uploadPhotoForm');
        // Set action form dengan ID koordinat dari marker
        form.action = `<?= site_url('koordinat/uploadPhotos/'); ?>${koordinatId}`;
        
        // Set nilai ID koordinat di input tersembunyi
        document.getElementById('upload_koordinat_id').value = koordinatId;

        var uploadModal = new bootstrap.Modal(document.getElementById('uploadPhotoModal'));
        uploadModal.show();
    }

    const filterSumberData = document.getElementById('filterSumberData');
    const filterKota = document.getElementById('filterKota');
    const filterKecamatan = document.getElementById('filterKecamatan');
    const filterKelurahan = document.getElementById('filterKelurahan');

    function populateSelect(selectElement, data, placeholder, valueKey, textKey) {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[textKey];
            selectElement.appendChild(option);
        });
    }

    filterSumberData.addEventListener('change', loadMarkers);
    filterKota.addEventListener('change', function() {
        const idKotakab = this.value;
        filterKecamatan.value = '';
        populateSelect(filterKelurahan, [], 'Semua Kelurahan', 'id_kel', 'nama_kel');
        filterKecamatan.disabled = true;
        filterKelurahan.disabled = true;

        if (idKotakab) {
            fetch(`<?= base_url('api/kecamatan_by_kotakab'); ?>?id_kotakab=${idKotakab}`)
                .then(response => response.json())
                .then(data => {
                    populateSelect(filterKecamatan, data, 'Semua Kecamatan', 'id_kec', 'nama_kec');
                    filterKecamatan.disabled = false;
                })
                .catch(error => console.error('Error fetching kecamatan:', error));
        }

        loadMarkers();
    });

    filterKecamatan.addEventListener('change', function() {
        const idKec = this.value;
        filterKelurahan.value = '';
        filterKelurahan.disabled = true;

        if (idKec) {
            fetch(`<?= base_url('api/kelurahan_by_kecamatan'); ?>?id_kec=${idKec}`)
                .then(response => response.json())
                .then(data => {
                    populateSelect(filterKelurahan, data, 'Semua Kelurahan', 'id_kel', 'nama_kel');
                    filterKelurahan.disabled = false;
                })
                .catch(error => console.error('Error fetching kelurahan:', error));
        }

        loadMarkers();
    });

    filterKelurahan.addEventListener('change', loadMarkers);

    document.addEventListener('DOMContentLoaded', function() {
        const exportLinks = document.querySelectorAll('.dropdown-menu a');

        exportLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                const baseUrl = this.href;
                const params = new URLSearchParams();
                const selectedSumberId = filterSumberData.value;
                const selectedKotakab = filterKota.value;
                const selectedKec = filterKecamatan.value;
                const selectedKel = filterKelurahan.value;

                if (selectedSumberId) params.append('sumber_data_id', selectedSumberId);
                if (selectedKotakab) params.append('id_kotakab', selectedKotakab);
                if (selectedKec) params.append('id_kec', selectedKec);
                if (selectedKel) params.append('id_kel', selectedKel);

                let finalUrl = baseUrl;
                if (params.toString()) {
                    finalUrl += '?' + params.toString();
                }

                window.location.href = finalUrl;
            });
        });
    });

    loadMarkers();
</script>