

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var map = L.map('world_map').setView([-2.5, 118.5], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var koordinatData = <?php echo $koordinat_json; ?>;

        var markers = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                var childCount = cluster.getChildCount();
                var c = ' marker-cluster-';
                if (childCount < 10) {
                    c += 'small';
                } else if (childCount < 100) {
                    c += 'medium';
                } else {
                    c += 'large';
                }
                return new L.DivIcon({
                    html: '<div><span>' + childCount + '</span></div>',
                    className: 'marker-cluster' + c,
                    iconSize: new L.Point(40, 40)
                });
            }
        });

        koordinatData.forEach(function(item) {
            var flaticonSVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="${item.warna}" d="M128 252.6C128 148.4 214 64 320 64C426 64 512 148.4 512 252.6C512 371.9 391.8 514.9 341.6 569.4C329.8 582.2 310.1 582.2 298.3 569.4C248.1 514.9 127.9 371.9 127.9 252.6zM320 320C355.3 320 384 291.3 384 256C384 220.7 355.3 192 320 192C284.7 192 256 220.7 256 256C256 291.3 284.7 320 320 320z"/></svg>`;

            var customIcon = L.divIcon({
                className: 'custom-pin',
                html: flaticonSVG, // Gunakan SVG yang sudah diwarnai
                iconSize: [24, 24],
                iconAnchor: [12, 24], // Atur ulang anchor ke bagian bawah pin
                popupAnchor: [0, -24] // Atur ulang anchor popup
            });

            var marker = L.marker([item.latitude, item.longitude], {icon: customIcon});

            var popupContent = `<b>Nama Sumber:</b> ${item.nama_sumber}<br><b>Nama:</b> ${item.nama_kec || 'Tidak ada nama'}`;
            marker.bindPopup(popupContent);

            markers.addLayer(marker);
        });

        map.addLayer(markers);
        map.fitBounds(markers.getBounds());
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
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
                    display: true // Tampilkan legend karena ada banyak dataset
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