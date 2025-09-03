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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Inisialisasi peta
    var map = L.map('mapid').setView([-6.2088, 106.8456], 13); // Koordinat Jakarta

    // Tambahkan tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Inisialisasi marker cluster group
    var markers = L.markerClusterGroup();

    // Fungsi untuk membuat marker
    function createCustomIcon(color) {
        // Tentukan warna ikon
        var iconUrl = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="' + color + '" d="M128 252.6C128 148.4 214 64 320 64C426 64 512 148.4 512 252.6C512 371.9 391.8 514.9 341.6 569.4C329.8 582.2 310.1 582.2 298.3 569.4C248.1 514.9 127.9 371.9 127.9 252.6zM320 320C355.3 320 384 291.3 384 256C384 220.7 355.3 192 320 192C284.7 192 256 220.7 256 256C256 291.3 284.7 320 320 320z"/></svg>');
        return L.icon({
            iconUrl: iconUrl,
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });
    }

    // Fungsi untuk memuat dan menampilkan data marker
    function loadMarkers(sumberId = '') {
        markers.clearLayers();
        map.removeLayer(markers);

        fetch(`<?= base_url('api/markers'); ?>?sumber_data_id=${sumberId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    if (item.latitude && item.longitude) {
                        var color = item.warna;
                        var customIcon = createCustomIcon(color);
                        var marker = L.marker([item.latitude, item.longitude], {
                            icon: customIcon
                        });

                        // Konten popup dengan tombol "Detail"
                        var popupContent = `
                            <strong>Sumber Data:</strong> ${item.nama_sumber}<br>
                            <strong>Kota/Kab:</strong> ${item.nama_kotakab || '-'}<br>
                            <strong>Kecamatan:</strong> ${item.nama_kec || '-'}<br>
                            <strong>Kelurahan:</strong> ${item.nama_kel || '-'}<br>
                            <strong>Latitude:</strong> ${item.latitude}<br>
                            <strong>Longitude:</strong> ${item.longitude}<br>
                            <hr>
                            <button class="btn btn-primary btn-sm" onclick="showDetailModal(${item.id_koordinat})">Detail</button>
                        `;

                        marker.bindPopup(popupContent);
                        markers.addLayer(marker);
                    }
                });
                map.addLayer(markers);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    // Fungsi untuk menampilkan modal dengan data detail
    function showDetailModal(idKoordinat) {
        // Ambil data dari API atau dari data yang sudah di-cache jika memungkinkan
        fetch(`<?= base_url('api/markers'); ?>?id_koordinat=${idKoordinat}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    var item = data[0];
                    var modalBody = document.getElementById('modalBodyContent');
                    modalBody.innerHTML = ''; // Kosongkan konten sebelumnya

                    // Buat konten modal
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

                    // Tambahkan data keterangan jika ada
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

                    modalBody.innerHTML = contentHtml;

                    // Tampilkan modal (menggunakan Bootstrap 5)
                    var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                    detailModal.show();
                } else {
                    console.log('Data tidak ditemukan.');
                }
            })
            .catch(error => console.error('Error fetching data for modal:', error));
    }

    // Event listener untuk filter
    document.getElementById('filterSumberData').addEventListener('change', function() {
        var selectedId = this.value;
        loadMarkers(selectedId);
    });

    loadMarkers();
</script>