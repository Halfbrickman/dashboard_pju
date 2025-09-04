<script>
    // =========================================================================
    // BLOK UTAMA UNTUK PETA LEAFLET, MARKER, DAN MODAL DETAIL
    // =========================================================================
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Inisialisasi Peta
        var map = L.map('world_map').setView([-2.5, 118.5], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // 2. Ambil Data & Buat Peta untuk Pencarian Cepat
        var koordinatData = <?php echo $koordinat_json; ?>;
        var koordinatMap = {};
        koordinatData.forEach(function(item) {
            koordinatMap[item.id_koordinat] = item;
        });

        // 3. Inisialisasi Grup Cluster untuk Marker
        var markers = L.markerClusterGroup({
            // Opsi tambahan untuk cluster bisa ditaruh di sini, contoh:
            // maxClusterRadius: 80,
            // chunkedLoading: true
        });

        // 4. Proses Pembuatan Semua Marker (Metode Cepat)
        var markerList = []; // Buat array kosong untuk menampung marker

        koordinatData.forEach(function(item) {
            // Definisikan ikon SVG kustom dengan warna dinamis
            var flaticonSVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="${item.warna}" d="M128 252.6C128 148.4 214 64 320 64C426 64 512 148.4 512 252.6C512 371.9 391.8 514.9 341.6 569.4C329.8 582.2 310.1 582.2 298.3 569.4C248.1 514.9 127.9 371.9 127.9 252.6zM320 320C355.3 320 384 291.3 384 256C384 220.7 355.3 192 320 192C284.7 192 256 220.7 256 256C256 291.3 284.7 320 320 320z"/></svg>`;
            
            var customIcon = L.divIcon({
                className: 'custom-pin', // Class CSS jika ada
                html: flaticonSVG,
                iconSize: [24, 24],
                iconAnchor: [12, 24],
                popupAnchor: [0, -24]
            });

            // Buat objek marker dengan ikon kustom
            var marker = L.marker([item.latitude, item.longitude], {
                icon: customIcon
            });

            // Buat konten untuk popup
            var popupContent = `
                <b>Nama Sumber:</b> ${item.nama_sumber}<br>
                <b>Kecamatan:</b> ${item.nama_kec || 'N/A'}<br>
                <button class="btn btn-sm btn-info mt-2 detail-btn" 
                        data-bs-toggle="modal" 
                        data-bs-target="#detailModal" 
                        data-id="${item.id_koordinat}">
                    Lihat Detail
                </button>
            `;
            marker.bindPopup(popupContent);
            
            // Masukkan marker yang sudah jadi ke dalam array
            markerList.push(marker); 
        });

        // 5. Tambahkan semua marker sekaligus ke grup cluster
        markers.addLayers(markerList); 
        map.addLayer(markers);
        
        // Atur view peta agar semua marker terlihat
        if (markerList.length > 0) {
            map.fitBounds(markers.getBounds());
        }

        // 6. Event Listener untuk Tombol "Lihat Detail"
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('detail-btn')) {
                var koordinatId = e.target.getAttribute('data-id');
                var data = koordinatMap[koordinatId]; // Pencarian data super cepat

                if (data) {
                    var modalBody = document.getElementById('detail-content');
                    var contentHtml = `
                        <table class="table table-striped table-bordered">
                            <tbody>
                                <tr><th style="width: 30%;">ID Koordinat</th><td>${data.id_koordinat}</td></tr>
                                <tr><th>Sumber Data</th><td>${data.nama_sumber}</td></tr>
                                <tr><th>Kota/Kabupaten</th><td>${data.nama_kotakab || 'N/A'}</td></tr>
                                <tr><th>Kecamatan</th><td>${data.nama_kec || 'N/A'}</td></tr>
                                <tr><th>Kelurahan</th><td>${data.nama_kel || 'N/A'}</td></tr>
                                <tr><th>Latitude</th><td>${data.latitude}</td></tr>
                                <tr><th>Longitude</th><td>${data.longitude}</td></tr>
                                <tr><th>Nomor Gardu</th><td>${data.nomor_gardu || 'N/A'}</td></tr>
                                <tr><th>Daya PJU</th><td>${data.daya_pju || 'N/A'}</td></tr>
                                <tr><th>Kondisi PJU</th><td>${data.kondisi_pju || 'N/A'}</td></tr>
                            </tbody>
                        </table>
                    `;
                    modalBody.innerHTML = contentHtml;
                }
            }
        });
    });

    // =========================================================================
    // BLOK UNTUK CHART.JS (Terpisah dan sudah benar)
    // =========================================================================
    document.addEventListener("DOMContentLoaded", function() {
        var labels = <?= $labels ?>;
        var datasets = <?= $datasets ?>;

        new Chart(document.getElementById("chartjs-dashboard-line"), {
            type: "line",
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                maintainAspectRatio: false,
                legend: {
                    display: true
                },
                tooltips: {
                    intersect: false
                },
                hover: {
                    intersect: true
                },
                plugins: {
                    filler: {
                        propagate: false
                    }
                },
                scales: {
                    xAxes: [{
                        reverse: true,
                        gridLines: {
                            color: "rgba(0,0,0,0.0)"
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            stepSize: 1
                        },
                        display: true,
                        borderDash: [3, 3],
                        gridLines: {
                            color: "rgba(0,0,0,0.0)"
                        }
                    }]
                }
            }
        });
    });
</script>