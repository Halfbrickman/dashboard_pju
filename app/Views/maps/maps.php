<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">Peta Data</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="filterSumberData" class="form-label">Filter Sumber Data:</label>
                                <select class="form-select" id="filterSumberData">
                                    <option value="">Semua Sumber Data</option>
                                    <?php foreach ($sumber_data as $sumber) : ?>
                                        <option value="<?= $sumber['id_sumberdata']; ?>" data-color="<?= $sumber['warna']; ?>"><?= $sumber['nama_sumber']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-auto d-flex align-items-end">
                                <div class="btn-group">
                                    <button type="button" style="height: 40px; " class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-file-export"></i> Ekspor Data
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" id="exportKML" href="<?= base_url('map/exportKML'); ?>">Format KML</a></li>
                                        <li><a class="dropdown-item" id="exportExcel" href="<?= base_url('map/exportExcel'); ?>">Format Excel (.xlsx)</a></li>
                                        <li><a class="dropdown-item" id="exportPDF" href="<?= base_url('map/exportPDF'); ?>">Format PDF</a></li>
                                    </ul>
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

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBodyContent">
            </div>
            <div class="modal-footer" id="modalFooterButtons">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

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
                        <select id="edit_sumberdata" class="form-select"></select>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveEditButton">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<script>
    const isAdmin = <?= session()->get('role_id') == 1 ? 'true' : 'false'; ?>;
    let allMarkersData = [];
    let markerLayers = {};

    // Data dari PHP sekarang tersedia di JavaScript
    const allSumberData = <?= json_encode($sumber_data); ?>;
    const allKotaKab = <?= json_encode($kotakab); ?>;
    const allKecamatan = <?= json_encode($kecamatan); ?>;
    const allKelurahan = <?= json_encode($kelurahan); ?>;

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
            <strong>Sumber Data:</strong> ${item.nama_sumber}<br>
            <strong>Kota/Kab:</strong> ${item.nama_kotakab || '-'}<br>
            <strong>Kecamatan:</strong> ${item.nama_kec || '-'}<br>
            <strong>Kelurahan:</strong> ${item.nama_kel || '-'}<br>
            <strong>Latitude:</strong> ${item.latitude}<br>
            <strong>Longitude:</strong> ${item.longitude}<br>
            <hr>
        `;
        
        if (isAdmin) {
            popupContent += `
                <button onclick="openEditModal(${item.id_koordinat})" class="btn btn-warning btn-sm" style="border-radius: 10px; margin-right: 5px;">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete(${item.id_koordinat})" style="border-radius: 10px;">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
                <br><br>
            `;
        }
        
        popupContent += `
            <button class="btn btn-primary btn-sm" onclick="showDetailModal(${item.id_koordinat})">Detail</button>
            <a href="https://www.google.com/maps/search/?api=1&query=${item.latitude},${item.longitude}" target="_blank" class="btn btn-info btn-sm">Lihat di Google Maps</a>
        `;
        return popupContent;
    }

    function loadMarkers(sumberId = '') {
        markers.clearLayers();
        map.removeLayer(markers);
        allMarkersData = [];
        markerLayers = {};

        fetch(`<?= base_url('api/markers'); ?>?sumber_data_id=${sumberId}`)
            .then(response => response.json())
            .then(data => {
                allMarkersData = data;
                allMarkersData.forEach(item => {
                    if (item.latitude && item.longitude) {
                        var color = item.warna;
                        var customIcon = createCustomIcon(color);
                        var marker = L.marker([item.latitude, item.longitude], {
                            icon: customIcon
                        });
                        
                        markerLayers[item.id_koordinat] = marker;
                        marker.bindPopup(renderPopupContent(item));
                        markers.addLayer(marker);
                    }
                });
                map.addLayer(markers);
            })
            .catch(error => console.error('Error fetching data:', error));
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
        
        // 1. Isi dropdown Sumber Data
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

        // 2. Isi dropdown Kota/Kab
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
        
        // 3. Panggil fungsi untuk mengisi dropdown Kecamatan dan Kelurahan
        populateKecamatan(item.id_kotakab, item.id_kec);
        populateKelurahan(item.id_kec, item.id_kel);


        // Tampilkan modal
        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }

    function populateKecamatan(selectedKotaKabId, selectedKecamatanId = null) {
        const editKecamatanSelect = document.getElementById('edit_kecamatan');
        editKecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
        editKecamatanSelect.disabled = !selectedKotaKabId;
        
        if (selectedKotaKabId) {
            const filteredKecamatan = allKecamatan.filter(kec => kec.id_kotakab == selectedKotaKabId);
            filteredKecamatan.forEach(kecamatan => {
                const newOption = document.createElement('option');
                newOption.value = kecamatan.id_kec;
                newOption.textContent = kecamatan.nama_kec;
                if (kecamatan.id_kec == selectedKecamatanId) {
                    newOption.selected = true;
                }
                editKecamatanSelect.appendChild(newOption);
            });
        }
    }

    function populateKelurahan(selectedKecamatanId, selectedKelurahanId = null) {
        const editKelurahanSelect = document.getElementById('edit_kelurahan');
        editKelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';
        editKelurahanSelect.disabled = !selectedKecamatanId;

        if (selectedKecamatanId) {
            const filteredKelurahan = allKelurahan.filter(kel => kel.id_kec == selectedKecamatanId);
            filteredKelurahan.forEach(kelurahan => {
                const newOption = document.createElement('option');
                newOption.value = kelurahan.id_kel;
                newOption.textContent = kelurahan.nama_kel;
                if (kelurahan.id_kel == selectedKelurahanId) {
                    newOption.selected = true;
                }
                editKelurahanSelect.appendChild(newOption);
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


    // Fungsi untuk menyimpan perubahan ke database via AJAX (diambil dari modal)
    document.getElementById('saveEditButton').addEventListener('click', function() {
        const id = document.getElementById('edit_id_koordinat').value;
        const updatedData = {
            id_koordinat: document.getElementById('edit_id_koordinat').value,
            id_sumberdata: document.getElementById('edit_sumberdata').value,
            // Mengirim ID dari dropdown
            id_kotakab: document.getElementById('edit_kotakab').value,
            id_kec: document.getElementById('edit_kecamatan').value,
            id_kel: document.getElementById('edit_kelurahan').value,
            latitude: document.getElementById('edit_latitude').value,
            longitude: document.getElementById('edit_longitude').value,
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
                loadMarkers(document.getElementById('filterSumberData').value);
            } else {
                Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan data.', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving data:', error);
            Swal.fire('Gagal!', 'Terjadi kesalahan jaringan.', 'error');
        });
    });

    function showDetailModal(idKoordinat) {
        fetch(`<?= base_url('api/markers'); ?>?id_koordinat=${idKoordinat}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    var item = data[0];
                    var modalBody = document.getElementById('modalBodyContent');
                    var modalFooter = document.getElementById('modalFooterButtons'); 

                    if (modalBody) {
                        modalBody.innerHTML = '';
                    }
                    if (modalFooter) {
                        modalFooter.innerHTML = '';
                    }

                    let contentHtml = `
                    <h6>Informasi Umum</h6>
                    <ul>
                        <li><strong>Sumber Data:</strong> ${item.nama_sumber}</li>
                        <li><strong>Kota/Kab:</strong> ${item.nama_kotakab || '-'}</li>
                        <li><strong>Kecamatan:</strong> ${item.nama_kec || '-'}</li>
                        <li><strong>Kelurahan:</strong> ${item.nama_kel || '-'}</li>
                        <li><strong>Latitude:</strong> ${item.latitude}</li>
                        <li><strong>Longitude:</strong> ${item.longitude}</li>
                    </ul>
                    `;

                    if (item.keterangan && item.keterangan.length > 0) {
                        contentHtml += `
                        <hr>
                        <h6>Keterangan Tambahan</h6>
                        <ul>
                        `;
                        item.keterangan.forEach(keteranganItem => {
                            contentHtml += `<li><strong>${keteranganItem.jdl_keterangan}:</strong> ${keteranganItem.isi_keterangan}</li>`;
                        });
                        contentHtml += `</ul>`;
                    }

                    if (modalBody) {
                        modalBody.innerHTML = contentHtml;
                    }
                    if (modalFooter) {
                        modalFooter.innerHTML = `<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>`;
                    }
                    var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                    detailModal.show();
                } else {
                    console.log('Data tidak ditemukan.');
                }
            })
            .catch(error => console.error('Error fetching data for modal:', error));
    }

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
                        loadMarkers(document.getElementById('filterSumberData').value);
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

    const filterSelect = document.getElementById('filterSumberData');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            var selectedId = this.value;
            loadMarkers(selectedId);
        });
    }

    loadMarkers();
    
    // --- TEMPAT KODE ANDA YANG LAIN DI SINI ---
    const ctx = document.getElementById('myChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: { /* ... */ },
            options: { /* ... */ }
        });
    }
    
    const datePicker = document.getElementById('myDatePicker');
    if (datePicker) {
        flatpickr(datePicker, {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
        });
    }
</script>