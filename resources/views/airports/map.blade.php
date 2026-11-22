<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airport Map - AeroFind</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">

    <!-- 1. Tambahkan CSS Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

    <style>
        /* Atur tinggi peta supaya muncul di layar */
        #airport-map {
            width: 100%;
            height: 650px;
            border-radius: 18px;
            overflow: hidden;
            background: #e5e7eb;
        }
    </style>
</head>
<body>

    <h1>AeroFind Airport Map</h1>
    <p>Discover airport locations across Southeast Asia.</p>

    <h2>Nearest Airport Recommendations</h2>

    <select id="region-select">
        <option value="">Select City/Municipality</option>
    </select>

    <button id="search-nearest">Find Nearest Airports</button>

    <div id="recommendation-results"></div>

    <!-- Wadah Peta Leaflet -->
    <div id="airport-map"></div>

    <!-- 2. Tambahkan JS Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {

           // Batas kawasan Asia Tenggara
            const southeastAsiaBounds = L.latLngBounds(
                [-13, 90],  // Barat daya
                [28, 142]   // Timur laut
            );

            // Buat peta tanpa pengulangan dunia
            const map = L.map('airport-map', {
                worldCopyJump: false,
                maxBounds: southeastAsiaBounds,
                maxBoundsViscosity: 1.0,
                zoomSnap: 1,
                zoomDelta: 1,
                inertia: false,
                zoomAnimation: true
            });

            // Tampilkan kawasan Asia Tenggara
            map.fitBounds(southeastAsiaBounds, {
                padding: [20, 20],
                animate: false
            });

            // Kunci zoom minimum berdasarkan tampilan awal
            const southeastAsiaMinZoom = map.getZoom();

            map.setMinZoom(southeastAsiaMinZoom);
            map.setMaxZoom(19);

            // Terapkan batas navigasi
            map.setMaxBounds(southeastAsiaBounds);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            minZoom: southeastAsiaMinZoom,
            maxZoom: 19,
            noWrap: true,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

            // 1. Ambil data bandara dan pasang marker ke peta
            try {
                const response = await fetch('/api/airports');
                const airports = await response.json();

                airports.forEach(airport => {
                    const marker = L.marker([
                        Number(airport.latitude_deg),
                        Number(airport.longitude_deg)
                    ]).addTo(map);

                    marker.bindPopup(`
                        <strong>${escapeHtml(airport.name)}</strong><br>
                        IATA: ${escapeHtml(airport.iata_code ?? '-')}<br>
                        ICAO: ${escapeHtml(airport.icao_code ?? '-')}<br>
                        City: ${escapeHtml(airport.municipality ?? '-')}<br>
                        Type: ${escapeHtml(airport.type)}
                    `);
                });
            } catch (error) {
                console.error("Failed to load airport data:", error);
            }

            // 2. Ambil daftar kota/kabupaten untuk dropdown region
            const regionSelect = document.getElementById('region-select');
            try {
                const regionResponse = await fetch('/api/regions');
                const regions = await regionResponse.json();

                regions.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.region_id;
                    option.textContent = `${region.region_name} (${region.country_code})`;
                    regionSelect.appendChild(option);
                });
            } catch (error) {
                console.error("Failed to load region data:", error);
            }

            // 3. Event listener untuk tombol cari bandara terdekat
            document.getElementById('search-nearest').addEventListener('click', async () => {
                const regionId = regionSelect.value;

                if (!regionId) {
                    alert('Please select a city/municipality first.');
                    return;
                }

                try {
                    const response = await fetch(`/api/recommendations/nearest?region_id=${regionId}`);
                    const result = await response.json();

                    if (!response.ok || result.status === 'error') {
                        alert(result.message || 'Failed to load recommendation data.');
                        return;
                    }

                    const container = document.getElementById('recommendation-results');
                    container.replaceChildren();

                    const heading = document.createElement('h3');
                    heading.textContent = `Nearest airports from ${result.region}`;
                    container.appendChild(heading);

                    result.recommendations.forEach((airport, index) => {
                        const item = document.createElement('div');
                        item.textContent = `${index + 1}. ${airport.name} — ${Number(airport.distance_km).toFixed(2)} km`;
                        container.appendChild(item);
                    });
                } catch (error) {
                    console.error("Failed to fetch recommendations:", error);
                }
            });

        });

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[char]);
        }
    </script>

</body>
</html>