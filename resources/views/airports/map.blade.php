
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Bandara - AeroFind</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <h1>Peta Bandara AeroFind</h1>
    <p>Temukan lokasi bandara di Asia Tenggara.</p>

    <h2>Rekomendasi Bandara Terdekat</h2>

<select id="region-select">
    <option value="">Pilih Kota/Kabupaten</option>
</select>

<button id="search-nearest">
    Cari Bandara Terdekat
</button>

<div id="recommendation-results"></div>

    <div id="airport-map"></div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {

            const map = L.map('airport-map')
                .setView([1.5, 110], 4);

            L.tileLayer(
                'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }
            ).addTo(map);

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
                    Kota: ${escapeHtml(airport.municipality ?? '-')}<br>
                    Tipe: ${escapeHtml(airport.type)}
                `);
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

        // Mengambil daftar kota/kabupaten
const regionResponse = await fetch('/api/regions');
const regions = await regionResponse.json();

const regionSelect = document.getElementById('region-select');

regions.forEach(region => {
    const option = document.createElement('option');

    option.value = region.id;
    option.textContent =
        `${region.region_name} (${region.country_code})`;

    regionSelect.appendChild(option);
});

// Mencari bandara terdekat
document.getElementById('search-nearest')
    .addEventListener('click', async () => {

        const regionId = regionSelect.value;

        if (!regionId) {
            alert('Pilih kota/kabupaten terlebih dahulu.');
            return;
        }

        const response = await fetch(
            `/api/recommendations/nearest?region_id=${regionId}`
        );

        const result = await response.json();

        const container = document.getElementById(
            'recommendation-results'
        );

        container.replaceChildren();

        const heading = document.createElement('h3');
        heading.textContent =
            `Bandara terdekat dari ${result.region}`;

        container.appendChild(heading);

        result.recommendations.forEach((airport, index) => {
            const item = document.createElement('div');

            item.textContent =
                `${index + 1}. ${airport.name} — ` +
                `${Number(airport.distance_km).toFixed(2)} km`;

            container.appendChild(item);
        });
    });
    </script>

</body>
</html>
