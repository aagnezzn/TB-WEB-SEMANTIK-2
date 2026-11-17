<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Navigasi Bandara - AeroSearch</title>

    <link rel="icon" type="image/svg+xml"
          href="{{ asset('images/icon.svg') }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        #navigation-map {
            height: calc(100vh - 100px);
            min-height: 520px;
            width: 100%;
            z-index: 0;
        }

        .leaflet-routing-container {
            display: none;
        }

        @media (max-width: 768px) {
            #navigation-map {
                height: 60vh;
                min-height: 400px;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 text-slate-800">

    @include('layouts.navbar')

    <main class="max-w-[1600px] mx-auto px-4 py-6">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-3xl md:text-4xl font-bold text-[#7FA8D6]">
                Navigasi Bandara
            </h1>

            <p class="text-slate-600 mt-2">
                Temukan rute perjalanan menuju bandara tujuan Anda.
            </p>
        </div>

        <!-- LAYOUT UTAMA -->
        <div class="grid grid-cols-1 lg:grid-cols-[360px_1fr] gap-5">

            <!-- PANEL KIRI -->
            <aside class="bg-white/90 backdrop-blur-md rounded-3xl border-2 border-white shadow-xl p-5 h-fit">

                <h2 class="text-xl font-bold text-[#7FA8D6] mb-5">
                    Pengaturan Rute
                </h2>

                <!-- LOKASI AWAL -->
                <div class="mb-5">

                    <label class="block text-sm font-semibold mb-2">
                        Lokasi Awal
                    </label>

                    <div class="bg-[#A8C6E7]/20 border border-[#A8C6E7] rounded-xl p-3">

                        <p id="origin-label" class="text-sm text-slate-600">
                            Belum ditentukan
                        </p>

                        <p id="origin-coordinates"
                           class="text-xs text-slate-400 mt-1">
                            Klik peta untuk memilih lokasi awal.
                        </p>

                    </div>

                </div>


                    <!-- PENCARIAN BANDARA TUJUAN -->
                    <div class="mb-5 relative">

                        <label for="airport-search"
                            class="block text-sm font-semibold mb-2">
                            Bandara Tujuan
                        </label>

                        <!-- Input pencarian -->
                        <div class="relative">

                            <input
                                type="text"
                                id="airport-search"
                                autocomplete="off"
                                placeholder="Cari nama bandara atau kode IATA..."
                                class="w-full p-3 pr-10 rounded-xl border border-[#A8C6E7]
                                    bg-white focus:outline-none focus:ring-2
                                    focus:ring-[#7FA8D6]">

                            <!-- Ikon pencarian -->
                            <span class="absolute right-4 top-3.5 text-[#7FA8D6]">
                                🔍
                            </span>

                        </div>

                        <!-- Hasil pencarian -->
                        <div id="airport-results"
                            class="hidden absolute left-0 right-0 top-full mt-2
                                    bg-white border border-[#A8C6E7] rounded-xl
                                    shadow-xl max-h-64 overflow-y-auto z-[1000]">

                        </div>

                        <!-- Bandara terpilih -->
                        <div id="airport-selected"
                            class="hidden mt-3 p-3 rounded-xl
                                    bg-[#A8C6E7]/20 border border-[#A8C6E7]">

                            <p class="text-xs text-slate-500 mb-1">
                                Bandara tujuan terpilih
                            </p>

                            <p id="selected-airport-name"
                            class="font-semibold text-[#7FA8D6] text-sm">
                            </p>

                            <button type="button"
                                    id="clear-airport"
                                    class="text-xs text-red-500 hover:text-red-700 mt-2">
                                Ganti bandara
                            </button>

                        </div>

                    </div>

                <!-- TOMBOL -->
                <button id="find-route"
                    class="w-full bg-[#7FA8D6] hover:bg-[#6893C7] text-white font-bold py-3 rounded-xl shadow-md transition">

                    Cari Rute Navigasi
                </button>

                <button id="reset-route"
                    class="w-full mt-3 bg-[#FFE08A] hover:bg-[#FFD66B] text-slate-700 font-semibold py-3 rounded-xl transition">

                    Atur Ulang
                </button>

                <div class="border-t border-slate-200 my-6"></div>

                <!-- INFORMASI PERJALANAN -->
                <h3 class="font-bold text-slate-700 mb-3">
                    Informasi Perjalanan
                </h3>

                <div class="grid grid-cols-2 gap-3">

                    <div class="bg-[#A8C6E7]/20 rounded-xl p-3">
                        <p class="text-xs text-slate-500">
                            Jarak
                        </p>

                        <p id="route-distance"
                           class="text-lg font-bold text-[#7FA8D6]">
                            - km
                        </p>
                    </div>

                    <div class="bg-[#FFE08A]/40 rounded-xl p-3">
                        <p class="text-xs text-slate-500">
                            Estimasi Waktu
                        </p>

                        <p id="route-duration"
                           class="text-lg font-bold text-slate-700">
                            -
                        </p>
                    </div>

                </div>

                <p id="route-status"
                   class="text-sm text-slate-500 mt-4">
                    Tentukan lokasi awal dan bandara tujuan untuk melihat rute.
                </p>

                <p class="text-xs text-slate-400 mt-5">
                    Rute jalan disediakan melalui layanan OpenStreetMap
                    dan OSRM. Estimasi dapat berbeda dari kondisi perjalanan
                    sebenarnya.
                </p>

            </aside>

            <!-- PETA KANAN -->
            <section class="bg-white/90 backdrop-blur-md rounded-3xl border-2 border-white shadow-xl p-3">

                <div class="flex justify-between items-center px-2 py-3">

                    <div>
                        <h2 class="font-bold text-[#7FA8D6]">
                            Peta Navigasi
                        </h2>

                        <p class="text-xs text-slate-500">
                            Klik peta untuk menentukan lokasi awal.
                        </p>
                    </div>

                    <span class="text-xs bg-[#FFE08A] px-3 py-1 rounded-full font-semibold">
                        AeroSearch Map
                    </span>

                </div>

                <div id="navigation-map"
                     class="rounded-2xl overflow-hidden">
                </div>

            </section>

        </div>

    </main>

    @include('layouts.footer')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // =====================================
        // 1. INISIALISASI PETA
        // =====================================

        // =====================================
        // 1. INISIALISASI PETA ASEAN
        // =====================================

        // Batas wilayah peta ASEAN
        const aseanBounds = L.latLngBounds(
            [-13, 90],  // Barat Daya
            [28, 142]   // Timur Laut
        );

        // Inisialisasi peta
        const map = L.map('navigation-map', {

            center: [3, 115],
            zoom: 5,

            // Batas pergerakan peta
            maxBounds: aseanBounds,
            maxBoundsViscosity: 1.0,

            // Batas zoom
            minZoom: 4,
            maxZoom: 19,

            // Kontrol zoom
            zoomControl: true,

            // Mencegah peta berulang ke sisi dunia lainnya
            worldCopyJump: false

        });

        // Pastikan seluruh area batas dapat terlihat
        const minimumZoom = map.getBoundsZoom(aseanBounds);

        map.setMinZoom(minimumZoom);

        // Atur posisi awal
        map.setView(
            [3, 115],
            Math.max(5, map.getMinZoom())
        );

        // Tile OpenStreetMap
        L.tileLayer(
            'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
                noWrap: true
            }
        ).addTo(map);

        window.addEventListener('resize', function () {
            map.invalidateSize();
        });


        // =====================================
        // 2. ELEMEN HALAMAN
        // =====================================

        const originLabel = document.getElementById('origin-label');
        const originCoordinates = document.getElementById('origin-coordinates');

        // Elemen pencarian bandara
        const airportSearch = document.getElementById('airport-search');
        const airportResults = document.getElementById('airport-results');

        const airportSelected = document.getElementById('airport-selected');
        const selectedAirportName = document.getElementById('selected-airport-name');
        const clearAirportButton = document.getElementById('clear-airport');

        // Data bandara dari Laravel
        const airportData = @json($airportData);

        const distanceElement = document.getElementById('route-distance');
        const durationElement = document.getElementById('route-duration');

        const statusElement = document.getElementById('route-status');


        // =====================================
        // 3. VARIABEL RUTE
        // =====================================

        let origin = null;
        let destination = null;

        let originMarker = null;
        let destinationMarker = null;
        let routingControl = null;


        // =====================================
        // 4. MENGATUR LOKASI AWAL
        // =====================================

        function setOrigin(lat, lng) {

            origin = L.latLng(lat, lng);

            if (originMarker) {
                map.removeLayer(originMarker);
            }

            originMarker = L.marker(origin)
                .addTo(map)
                .bindPopup('Lokasi Awal')
                .openPopup();

            originLabel.textContent = 'Lokasi yang dipilih pada peta';

            originCoordinates.textContent =
                lat.toFixed(6) + ', ' + lng.toFixed(6);

            statusElement.textContent =
                'Lokasi awal berhasil ditentukan. Pilih bandara tujuan.';

        }


        // =====================================
        // 5. MEMILIH LOKASI AWAL DARI PETA
        // =====================================

        map.on('click', function (event) {

            setOrigin(
                event.latlng.lat,
                event.latlng.lng
            );

        });


        // =====================================
        // 6. MEMILIH BANDARA TUJUAN
        // =====================================

        // =====================================
        // 6. SEARCH BANDARA TUJUAN
        // =====================================

        function renderAirportResults(keyword) {

            airportResults.innerHTML = '';

            const query = keyword.trim().toLowerCase();

            if (!query) {
                airportResults.classList.add('hidden');
                return;
            }

            // Filter berdasarkan nama, IATA, ICAO,
            // kota/kabupaten, dan negara
            const results = airportData.filter(function (airport) {

                const searchableText = [
                    airport.name,
                    airport.iata,
                    airport.icao,
                    airport.municipality,
                    airport.country
                ].join(' ').toLowerCase();

                return searchableText.includes(query);

            }).slice(0, 20);


            if (results.length === 0) {

                airportResults.innerHTML = `
                    <div class="p-4 text-sm text-slate-500 text-center">
                        Bandara tidak ditemukan.
                    </div>
                `;

                airportResults.classList.remove('hidden');
                return;
            }


            results.forEach(function (airport) {

                const item = document.createElement('button');

                item.type = 'button';

                item.className =
                    'w-full text-left p-3 border-b border-slate-100 ' +
                    'hover:bg-[#A8C6E7]/20 transition duration-200';

                const name = document.createElement('p');

                name.className =
                    'font-semibold text-sm text-slate-800';

                name.textContent = airport.name;

                const details = document.createElement('p');

                details.className =
                    'text-xs text-slate-500 mt-1';

                details.textContent =
                    (airport.iata || '-') +
                    ' • ' +
                    (airport.municipality || '-') +
                    ' • ' +
                    (airport.country || '-');

                item.appendChild(name);
                item.appendChild(details);

                item.addEventListener('click', function () {

                    selectAirport(airport);

                });

                airportResults.appendChild(item);

            });

            airportResults.classList.remove('hidden');

        }


        // =====================================
        // 7. MEMILIH BANDARA DARI HASIL SEARCH
        // =====================================

        function selectAirport(airport) {

            const lat = Number(airport.lat);
            const lng = Number(airport.lng);

            if (
                !Number.isFinite(lat) ||
                !Number.isFinite(lng) ||
                Math.abs(lat) > 90 ||
                Math.abs(lng) > 180
            ) {
                statusElement.textContent =
                    'Koordinat bandara tidak valid.';
                return;
            }

            destination = L.latLng(lat, lng);

            // Hapus marker tujuan sebelumnya
            if (destinationMarker) {
                map.removeLayer(destinationMarker);
            }

            // Buat marker tujuan baru
            destinationMarker = L.marker(destination)
                .addTo(map)
                .bindPopup(airport.name);

            // Tampilkan bandara terpilih
            airportSearch.value = airport.name;

            selectedAirportName.textContent =
                airport.name +
                ' (' + (airport.iata || '-') + ')';

            airportSelected.classList.remove('hidden');

            airportResults.classList.add('hidden');

            // Arahkan peta ke lokasi bandara
            map.setView(destination, 10);

            statusElement.textContent =
                'Bandara tujuan berhasil dipilih. Silakan cari rute navigasi.';

        }


        // =====================================
        // 8. EVENT PENCARIAN
        // =====================================

        airportSearch.addEventListener('input', function () {

            // Hapus pilihan lama jika pengguna mulai mengetik ulang
            destination = null;

            if (destinationMarker) {
                map.removeLayer(destinationMarker);
                destinationMarker = null;
            }

            airportSelected.classList.add('hidden');

            renderAirportResults(this.value);

        });


        // =====================================
        // 9. TUTUP HASIL SEARCH
        // =====================================

        document.addEventListener('click', function (event) {

            if (
                !airportSearch.contains(event.target) &&
                !airportResults.contains(event.target)
            ) {
                airportResults.classList.add('hidden');
            }

        });


        // =====================================
        // 10. GANTI BANDARA
        // =====================================

        clearAirportButton.addEventListener('click', function () {

            destination = null;

            if (destinationMarker) {
                map.removeLayer(destinationMarker);
                destinationMarker = null;
            }

            airportSearch.value = '';

            airportSelected.classList.add('hidden');

            airportSearch.focus();

            statusElement.textContent =
                'Silakan cari bandara tujuan yang baru.';

        });


        // =====================================
        // 7. MEMBUAT RUTE PERJALANAN
        // =====================================

        function findRoute() {

            if (!origin) {
                statusElement.textContent =
                    'Silakan klik peta untuk menentukan lokasi awal.';
                return;
            }

            if (!destination) {
                statusElement.textContent =
                    'Silakan pilih bandara tujuan terlebih dahulu.';
                return;
            }

            if (routingControl) {
                map.removeControl(routingControl);
            }

            distanceElement.textContent = '- km';
            durationElement.textContent = '-';

            statusElement.textContent =
                'Sedang menghitung rute perjalanan...';

            routingControl = L.Routing.control({

                waypoints: [
                    origin,
                    destination
                ],

                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1'
                }),

                lineOptions: {
                    styles: [
                        {
                            color: '#7FA8D6',
                            opacity: 0.9,
                            weight: 7
                        }
                    ]
                },

                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                show: false,

                createMarker: function () {
                    return null;
                }

            }).addTo(map);


            // =================================
            // 8. HASIL JARAK DAN WAKTU
            // =================================

            routingControl.on('routesfound', function (event) {

                const route = event.routes[0];

                const distanceKm =
                    route.summary.totalDistance / 1000;

                const durationMinutes =
                    route.summary.totalTime / 60;

                distanceElement.textContent =
                    distanceKm.toFixed(2) + ' km';

                if (durationMinutes >= 60) {

                    const hours = Math.floor(
                        durationMinutes / 60
                    );

                    const minutes = Math.round(
                        durationMinutes % 60
                    );

                    durationElement.textContent =
                        hours + ' jam ' + minutes + ' menit';

                } else {

                    durationElement.textContent =
                        Math.round(durationMinutes) + ' menit';

                }

                statusElement.textContent =
                    'Rute berhasil ditemukan. Selamat perjalanan!';

            });


            routingControl.on('routingerror', function () {

                statusElement.textContent =
                    'Rute gagal ditemukan. Periksa koneksi atau koordinat lokasi.';

                distanceElement.textContent = '- km';
                durationElement.textContent = '-';

            });

        }


        document.getElementById('find-route')
            .addEventListener('click', findRoute);


        // =====================================
        // 9. MENGATUR ULANG PETA
        // =====================================

        document.getElementById('reset-route')
            .addEventListener('click', function () {

                origin = null;
                destination = null;

                airportSearch.value = '';

                airportResults.classList.add('hidden');

                airportSelected.classList.add('hidden');

                if (originMarker) {
                    map.removeLayer(originMarker);
                    originMarker = null;
                }

                if (destinationMarker) {
                    map.removeLayer(destinationMarker);
                    destinationMarker = null;
                }

                if (routingControl) {
                    map.removeControl(routingControl);
                    routingControl = null;
                }

                originLabel.textContent = 'Belum ditentukan';

                originCoordinates.textContent =
                    'Klik peta untuk memilih lokasi awal.';

                distanceElement.textContent = '- km';
                durationElement.textContent = '-';

                statusElement.textContent =
                    'Tentukan lokasi awal dan bandara tujuan untuk melihat rute.';

                map.setView([3, 115], 5);

            });


        // =====================================
        // 10. MEMBACA PARAMETER DARI DASHBOARD
        // =====================================

        const params = new URLSearchParams(window.location.search);

        const originLat = Number(params.get('origin_lat'));
        const originLng = Number(params.get('origin_lng'));

        const destinationLat = Number(params.get('destination_lat'));
        const destinationLng = Number(params.get('destination_lng'));

        const airportId = params.get('airport_id');


        if (
            params.has('origin_lat') &&
            params.has('origin_lng') &&
            Number.isFinite(originLat) &&
            Number.isFinite(originLng) &&
            Math.abs(originLat) <= 90 &&
            Math.abs(originLng) <= 180
        ) {
            setOrigin(originLat, originLng);
        }


        if (
            params.has('destination_lat') &&
            params.has('destination_lng') &&
            Number.isFinite(destinationLat) &&
            Number.isFinite(destinationLng) &&
            Math.abs(destinationLat) <= 90 &&
            Math.abs(destinationLng) <= 180
        ) {

            destination = L.latLng(
                destinationLat,
                destinationLng
            );

            if (airportId) {

                const selectedAirport = airportData.find(function (airport) {
                    return String(airport.id) === String(airportId);
                });

                if (selectedAirport) {
                    selectAirport(selectedAirport);
                }

            }

            const selectedOption =
                airportSelect.options[airportSelect.selectedIndex];

            const airportName =
                selectedOption && selectedOption.value
                    ? selectedOption.dataset.name
                    : params.get('airport_name') || 'Bandara tujuan';

            destinationMarker = L.marker(destination)
                .addTo(map)
                .bindPopup(airportName);

            // Jika halaman dibuka dari tombol rekomendasi,
            // langsung hitung rute.
            if (origin) {
                findRoute();
            }
        }

    });
    </script>

</body>
</html>