<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Navigasi Bandara - AeroSearch</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

        body { font-family: 'Inter', sans-serif; }

        #navigation-map {
            height: calc(100vh - 100px);
            min-height: 520px;
            width: 100%;
            z-index: 0;
        }

        .mode-item { transition: all .15s ease; }
        .mode-item.active {
            background: rgba(168, 198, 231, .35);
            border-color: #7FA8D6;
            box-shadow: 0 0 0 2px rgba(127, 168, 214, .35);
        }

        .skeleton {
            background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 6px;
        }
        @keyframes shimmer { to { background-position: -200% 0; } }

        @media (max-width: 768px) {
            #navigation-map { height: 60vh; min-height: 400px; }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 text-slate-800">

    @include('layouts.navbar')

    <main class="max-w-[1600px] mx-auto px-4 py-6">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-3xl md:text-4xl font-bold text-[#7FA8D6]">Navigasi Bandara</h1>
            <p class="text-slate-600 mt-2">Temukan rute perjalanan menuju bandara tujuan Anda.</p>
        </div>

        <!-- LAYOUT UTAMA -->
        <div class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-5 items-start">

            <!-- PANEL KIRI -->
            <aside class="bg-white/90 backdrop-blur-md rounded-3xl border-2 border-white shadow-xl p-5 h-fit">

                <h2 class="text-xl font-bold text-[#7FA8D6] mb-5">Pengaturan Rute</h2>

                <!-- LOKASI AWAL -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold mb-2">Lokasi Awal</label>
                    <div class="bg-[#A8C6E7]/20 border border-[#A8C6E7] rounded-xl p-3">
                        <p id="origin-label" class="text-sm text-slate-600">Belum ditentukan</p>
                        <p id="origin-coordinates" class="text-xs text-slate-400 mt-1">
                            Klik peta untuk memilih lokasi awal.
                        </p>
                    </div>
                </div>

                <!-- PENCARIAN BANDARA TUJUAN -->
                <div class="mb-5 relative">
                    <label for="airport-search" class="block text-sm font-semibold mb-2">Bandara Tujuan</label>

                    <div class="relative">
                        <input type="text" id="airport-search" autocomplete="off"
                            placeholder="Cari nama bandara atau kode IATA..."
                            class="w-full p-3 pr-10 rounded-xl border border-[#A8C6E7] bg-white focus:outline-none focus:ring-2 focus:ring-[#7FA8D6]">
                        <span class="absolute right-4 top-3.5 text-[#7FA8D6]">🔍</span>
                    </div>

                    <div id="airport-results"
                        class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-[#A8C6E7] rounded-xl shadow-xl max-h-64 overflow-y-auto z-[1000]">
                    </div>

                    <div id="airport-selected" class="hidden mt-3 p-3 rounded-xl bg-[#A8C6E7]/20 border border-[#A8C6E7]">
                        <p class="text-xs text-slate-500 mb-1">Bandara tujuan terpilih</p>
                        <p id="selected-airport-name" class="font-semibold text-[#7FA8D6] text-sm"></p>
                        <button type="button" id="clear-airport" class="text-xs text-red-500 hover:text-red-700 mt-2">
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

                <!-- PERBANDINGAN MODA TRANSPORTASI -->
                <h3 class="font-bold text-slate-700 mb-3">Perbandingan Moda Transportasi</h3>

                <div id="mode-list" class="space-y-2">
                    <p class="text-sm text-slate-400">
                        Perbandingan waktu akan muncul setelah rute dicari.
                    </p>
                </div>

                <!-- RINGKASAN MODA TERPILIH -->
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="bg-[#A8C6E7]/20 rounded-xl p-3">
                        <p class="text-xs text-slate-500">Jarak</p>
                        <p id="route-distance" class="text-lg font-bold text-[#7FA8D6]">- km</p>
                    </div>
                    <div class="bg-[#FFE08A]/40 rounded-xl p-3">
                        <p class="text-xs text-slate-500">Estimasi Waktu</p>
                        <p id="route-duration" class="text-lg font-bold text-slate-700">-</p>
                    </div>
                </div>

                <p id="route-status" class="text-sm text-slate-500 mt-4">
                    Tentukan lokasi awal dan bandara tujuan untuk melihat rute.
                </p>

                <a id="transit-link" href="#" target="_blank" rel="noopener"
                   class="hidden mt-3 text-sm font-semibold text-[#7FA8D6] hover:underline">
                    Lihat jadwal transportasi umum di Google Maps ↗
                </a>

                <p class="text-xs text-slate-400 mt-5">
                    Rute disediakan oleh OpenStreetMap dan OSRM (FOSSGIS). Waktu
                    <b>Motor</b> dan <b>Transportasi umum</b> adalah perkiraan
                    berdasarkan rute jalan raya, bukan data jadwal real-time.
                    Estimasi dapat berbeda dari kondisi perjalanan sebenarnya.
                </p>

            </aside>

            <!-- PETA KANAN -->
            <section class="bg-white/90 backdrop-blur-md rounded-3xl border-2 border-white shadow-xl p-3 lg:sticky lg:top-4 self-start">

                <div class="flex justify-between items-center px-2 py-3">
                    <div>
                        <h2 class="font-bold text-[#7FA8D6]">Peta Navigasi</h2>
                        <p class="text-xs text-slate-500">Klik peta untuk menentukan lokasi awal.</p>
                    </div>
                    <span class="text-xs bg-[#FFE08A] px-3 py-1 rounded-full font-semibold">AeroSearch Map</span>
                </div>

                <div id="navigation-map" class="rounded-2xl overflow-hidden"></div>

            </section>

        </div>

    </main>

    @include('layouts.footer')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // =====================================
        // 1. INISIALISASI PETA ASEAN
        // =====================================

        const aseanBounds = L.latLngBounds([-13, 90], [28, 142]);

        const map = L.map('navigation-map', {
            center: [3, 115],
            zoom: 5,
            maxBounds: aseanBounds,
            maxBoundsViscosity: 1.0,
            minZoom: 4,
            maxZoom: 19,
            zoomControl: true,
            worldCopyJump: false
        });

        map.setMinZoom(map.getBoundsZoom(aseanBounds));
        map.setView([3, 115], Math.max(5, map.getMinZoom()));

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
            noWrap: true
        }).addTo(map);

        window.addEventListener('resize', function () { map.invalidateSize(); });


        // =====================================
        // 2. ELEMEN HALAMAN
        // =====================================

        const originLabel = document.getElementById('origin-label');
        const originCoordinates = document.getElementById('origin-coordinates');

        const airportSearch = document.getElementById('airport-search');
        const airportResults = document.getElementById('airport-results');
        const airportSelected = document.getElementById('airport-selected');
        const selectedAirportName = document.getElementById('selected-airport-name');
        const clearAirportButton = document.getElementById('clear-airport');

        const airportData = @json($airportData);

        const distanceElement = document.getElementById('route-distance');
        const durationElement = document.getElementById('route-duration');
        const statusElement = document.getElementById('route-status');
        const modeListElement = document.getElementById('mode-list');
        const transitLink = document.getElementById('transit-link');


        // =====================================
        // 3. KONFIGURASI MODA TRANSPORTASI
        // =====================================

        // Layanan routing OSRM (FOSSGIS) dengan profil berbeda.
        const ROUTING_BASE = 'https://routing.openstreetmap.de/routed-';

        const MODES = [
            { key: 'driving', label: 'Mengemudi',         icon: '🚗', profile: 'car',  color: '#4285F4' },
            { key: 'motor',   label: 'Motor',             icon: '🏍️', derived: true,   color: '#F59E0B' },
            { key: 'transit', label: 'Transportasi umum', icon: '🚌', derived: true,   color: '#0F9D58' },
            { key: 'walking', label: 'Jalan kaki',        icon: '🚶', profile: 'foot', color: '#7C3AED', dashed: true },
            { key: 'cycling', label: 'Bersepeda',         icon: '🚲', profile: 'bike', color: '#E11D48' }
        ];

        // Parameter perkiraan (silakan disesuaikan)
        const MOTOR_TIME_FACTOR = 0.85;     // motor ~15% lebih cepat dari mobil
        const TRANSIT_AVG_SPEED_KMH = 25;   // kecepatan rata-rata angkutan umum
        const TRANSIT_OVERHEAD_MIN = 15;    // waktu tunggu + berjalan ke halte

        const BEST_MODE = { key: 'best', label: 'Moda transportasi terbaik', icon: '⭐' };


        // =====================================
        // 4. VARIABEL STATE
        // =====================================

        let origin = null;
        let destination = null;

        let originMarker = null;
        let destinationMarker = null;

        let results = {};              // hasil per moda
        let selectedMode = 'best';
        let requestId = 0;             // mencegah hasil lama menimpa hasil baru

        const routeLayer = L.layerGroup().addTo(map);


        // =====================================
        // 5. FUNGSI BANTU
        // =====================================

        function formatDuration(seconds) {
            const totalMinutes = Math.max(1, Math.round(seconds / 60));

            if (totalMinutes < 60) {
                return totalMinutes + ' mnt';
            }

            const days = Math.floor(totalMinutes / 1440);
            const hours = Math.floor((totalMinutes % 1440) / 60);
            const minutes = totalMinutes % 60;

            if (days > 0) {
                return days + ' hr ' + hours + ' jam';
            }

            return hours + ' jam' + (minutes ? ' ' + minutes + ' mnt' : '');
        }

        function formatDistance(meters) {
            const km = meters / 1000;
            return (km < 10 ? km.toFixed(1) : Math.round(km).toString()) + ' km';
        }

        function getMode(key) {
            return MODES.find(function (m) { return m.key === key; });
        }

        function getBestKey() {
            let bestKey = null;
            let bestTime = Infinity;

            MODES.forEach(function (mode) {
                const r = results[mode.key];
                if (r && r.duration < bestTime) {
                    bestTime = r.duration;
                    bestKey = mode.key;
                }
            });

            return bestKey;
        }

        function resolveActiveKey() {
            return selectedMode === 'best' ? getBestKey() : selectedMode;
        }

        async function fetchRoute(profile, from, to) {

            const url = ROUTING_BASE + profile +
                '/route/v1/driving/' +
                from.lng + ',' + from.lat + ';' + to.lng + ',' + to.lat +
                '?overview=full&geometries=geojson';

            const controller = new AbortController();
            const timer = setTimeout(function () { controller.abort(); }, 20000);

            try {
                const response = await fetch(url, { signal: controller.signal });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const data = await response.json();

                if (data.code !== 'Ok' || !data.routes || !data.routes.length) {
                    throw new Error(data.code || 'NoRoute');
                }

                const route = data.routes[0];

                return {
                    distance: route.distance,
                    duration: route.duration,
                    geometry: route.geometry
                };

            } finally {
                clearTimeout(timer);
            }
        }


        // =====================================
        // 6. TAMPILAN DAFTAR MODA
        // =====================================

        function buildModeButton(mode, data, options) {

            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.mode = mode.key;

            button.className =
                'mode-item w-full flex items-center gap-3 text-left p-3 rounded-xl ' +
                'border border-slate-200 bg-white hover:bg-[#A8C6E7]/10';

            if (options.active) {
                button.classList.add('active');
            }

            const icon = document.createElement('span');
            icon.className = 'text-2xl w-8 text-center';
            icon.textContent = mode.icon;

            const info = document.createElement('div');
            info.className = 'flex-1 min-w-0';

            const title = document.createElement('p');
            title.className = 'text-sm font-semibold text-slate-800 truncate';
            title.textContent = mode.label;

            const sub = document.createElement('p');
            sub.className = 'text-xs text-slate-500 mt-0.5';

            if (options.subtitle) {
                sub.textContent = options.subtitle;
            } else if (data) {
                sub.textContent = formatDistance(data.distance) + (data.estimated ? ' • perkiraan' : '');
            } else {
                sub.textContent = options.loading ? '' : 'Tidak tersedia';
            }

            info.appendChild(title);
            info.appendChild(sub);

            const time = document.createElement('div');
            time.className = 'text-right';

            if (options.loading) {
                time.innerHTML = '<div class="skeleton h-5 w-14"></div>';
            } else {
                const t = document.createElement('p');
                t.className = 'text-base font-bold ' + (data ? 'text-[#7FA8D6]' : 'text-slate-300');
                t.textContent = data ? formatDuration(data.duration) : '—';
                time.appendChild(t);
            }

            button.appendChild(icon);
            button.appendChild(info);
            button.appendChild(time);

            if (!options.loading && data) {
                button.addEventListener('click', function () {
                    selectMode(mode.key);
                });
            } else {
                button.disabled = true;
                button.classList.add('opacity-60', 'cursor-not-allowed');
            }

            return button;
        }

        function renderModeList(isLoading) {

            modeListElement.innerHTML = '';

            // ---- Moda transportasi terbaik ----
            const bestKey = isLoading ? null : getBestKey();
            const bestData = bestKey ? results[bestKey] : null;

            const bestButton = buildModeButton(
                BEST_MODE,
                bestData,
                {
                    loading: isLoading,
                    active: selectedMode === 'best',
                    subtitle: bestKey
                        ? 'Tercepat: ' + getMode(bestKey).label + ' • ' + formatDistance(bestData.distance)
                        : (isLoading ? '' : 'Tidak tersedia')
                }
            );

            if (!isLoading && bestData) {
                bestButton.classList.add('border-[#FFD66B]', 'bg-[#FFF7D6]/60');
            }

            modeListElement.appendChild(bestButton);

            // ---- Moda lainnya ----
            MODES.forEach(function (mode) {
                modeListElement.appendChild(
                    buildModeButton(mode, results[mode.key], {
                        loading: isLoading,
                        active: selectedMode === mode.key
                    })
                );
            });
        }


        // =====================================
        // 7. MENGGAMBAR RUTE DI PETA
        // =====================================

        function drawRoutes() {

            routeLayer.clearLayers();

            const activeKey = resolveActiveKey();
            if (!activeKey || !results[activeKey]) return;

            // Rute alternatif (abu-abu), hanya moda dengan rute asli
            MODES.forEach(function (mode) {

                const data = results[mode.key];

                if (!data || mode.derived || mode.key === activeKey) return;

                const line = L.geoJSON(data.geometry, {
                    style: { color: '#94A3B8', weight: 5, opacity: 0.65 }
                });

                line.on('click', function () { selectMode(mode.key); });
                line.bindTooltip(mode.icon + ' ' + mode.label + ' • ' + formatDuration(data.duration), { sticky: true });
                line.addTo(routeLayer);
            });

            // Rute aktif
            const activeMode = getMode(activeKey);
            const activeData = results[activeKey];

            const activeLine = L.geoJSON(activeData.geometry, {
                style: {
                    color: activeMode.color,
                    weight: activeMode.dashed ? 6 : 7,
                    opacity: 0.95,
                    dashArray: activeMode.dashed ? '1, 12' : null,
                    lineCap: 'round'
                }
            }).addTo(routeLayer);

            map.fitBounds(activeLine.getBounds(), { padding: [50, 50] });
        }

        function updateSummary() {

            const activeKey = resolveActiveKey();
            const data = activeKey ? results[activeKey] : null;

            if (!data) {
                distanceElement.textContent = '- km';
                durationElement.textContent = '-';
                transitLink.classList.add('hidden');
                return;
            }

            distanceElement.textContent = formatDistance(data.distance);
            durationElement.textContent = formatDuration(data.duration);

            // Tautan jadwal transportasi umum
            if (activeKey === 'transit' && origin && destination) {
                transitLink.href =
                    'https://www.google.com/maps/dir/?api=1' +
                    '&origin=' + origin.lat + ',' + origin.lng +
                    '&destination=' + destination.lat + ',' + destination.lng +
                    '&travelmode=transit';
                transitLink.classList.remove('hidden');
            } else {
                transitLink.classList.add('hidden');
            }

            const mode = getMode(activeKey);

            statusElement.textContent =
                (selectedMode === 'best' ? 'Moda terbaik: ' : 'Moda: ') + mode.label +
                (data.estimated ? ' (perkiraan). ' : '. ') +
                'Selamat perjalanan!';
        }

        function selectMode(key) {
            selectedMode = key;
            renderModeList(false);
            drawRoutes();
            updateSummary();
        }

        function resetResults() {
            requestId++;   // batalkan permintaan yang sedang berjalan
            results = {};
            selectedMode = 'best';
            routeLayer.clearLayers();
            distanceElement.textContent = '- km';
            durationElement.textContent = '-';
            transitLink.classList.add('hidden');
            modeListElement.innerHTML =
                '<p class="text-sm text-slate-400">Perbandingan waktu akan muncul setelah rute dicari.</p>';
        }


        // =====================================
        // 8. MENGATUR LOKASI AWAL
        // =====================================

        function setOrigin(lat, lng) {

            origin = L.latLng(lat, lng);

            if (originMarker) {
                map.removeLayer(originMarker);
            }

            originMarker = L.marker(origin).addTo(map).bindPopup('Lokasi Awal').openPopup();

            originLabel.textContent = 'Lokasi yang dipilih pada peta';
            originCoordinates.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);

            resetResults();

            statusElement.textContent = destination
                ? 'Lokasi awal diperbarui. Klik "Cari Rute Navigasi".'
                : 'Lokasi awal berhasil ditentukan. Pilih bandara tujuan.';
        }

        map.on('click', function (event) {
            setOrigin(event.latlng.lat, event.latlng.lng);
        });


        // =====================================
        // 9. PENCARIAN BANDARA TUJUAN
        // =====================================

        function renderAirportResults(keyword) {

            airportResults.innerHTML = '';

            const query = keyword.trim().toLowerCase();

            if (!query) {
                airportResults.classList.add('hidden');
                return;
            }

            const results = airportData.filter(function (airport) {
                const searchableText = [
                    airport.name, airport.iata, airport.icao,
                    airport.municipality, airport.country
                ].join(' ').toLowerCase();

                return searchableText.includes(query);
            }).slice(0, 20);

            if (results.length === 0) {
                airportResults.innerHTML =
                    '<div class="p-4 text-sm text-slate-500 text-center">Bandara tidak ditemukan.</div>';
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
                name.className = 'font-semibold text-sm text-slate-800';
                name.textContent = airport.name;

                const details = document.createElement('p');
                details.className = 'text-xs text-slate-500 mt-1';
                details.textContent =
                    (airport.iata || '-') + ' • ' +
                    (airport.municipality || '-') + ' • ' +
                    (airport.country || '-');

                item.appendChild(name);
                item.appendChild(details);

                item.addEventListener('click', function () { selectAirport(airport); });

                airportResults.appendChild(item);
            });

            airportResults.classList.remove('hidden');
        }

        function selectAirport(airport) {

            const lat = Number(airport.lat);
            const lng = Number(airport.lng);

            if (!Number.isFinite(lat) || !Number.isFinite(lng) ||
                Math.abs(lat) > 90 || Math.abs(lng) > 180) {
                statusElement.textContent = 'Koordinat bandara tidak valid.';
                return;
            }

            destination = L.latLng(lat, lng);

            if (destinationMarker) {
                map.removeLayer(destinationMarker);
            }

            destinationMarker = L.marker(destination).addTo(map).bindPopup(airport.name);

            airportSearch.value = airport.name;
            selectedAirportName.textContent = airport.name + ' (' + (airport.iata || '-') + ')';

            airportSelected.classList.remove('hidden');
            airportResults.classList.add('hidden');

            resetResults();

            map.setView(destination, 10);

            statusElement.textContent =
                'Bandara tujuan berhasil dipilih. Silakan cari rute navigasi.';
        }

        airportSearch.addEventListener('input', function () {

            destination = null;

            if (destinationMarker) {
                map.removeLayer(destinationMarker);
                destinationMarker = null;
            }

            airportSelected.classList.add('hidden');
            resetResults();
            renderAirportResults(this.value);
        });

        document.addEventListener('click', function (event) {
            if (!airportSearch.contains(event.target) && !airportResults.contains(event.target)) {
                airportResults.classList.add('hidden');
            }
        });

        clearAirportButton.addEventListener('click', function () {

            destination = null;

            if (destinationMarker) {
                map.removeLayer(destinationMarker);
                destinationMarker = null;
            }

            airportSearch.value = '';
            airportSelected.classList.add('hidden');
            resetResults();
            airportSearch.focus();

            statusElement.textContent = 'Silakan cari bandara tujuan yang baru.';
        });


        // =====================================
        // 10. MENCARI RUTE UNTUK SEMUA MODA
        // =====================================

        async function findRoute() {

            if (!origin) {
                statusElement.textContent = 'Silakan klik peta untuk menentukan lokasi awal.';
                return;
            }

            if (!destination) {
                statusElement.textContent = 'Silakan pilih bandara tujuan terlebih dahulu.';
                return;
            }

            const currentRequest = ++requestId;

            results = {};
            selectedMode = 'best';
            routeLayer.clearLayers();

            distanceElement.textContent = '- km';
            durationElement.textContent = '-';
            transitLink.classList.add('hidden');

            statusElement.textContent = 'Sedang menghitung rute untuk semua moda transportasi...';
            renderModeList(true);

            // Ambil rute asli (mobil, jalan kaki, sepeda) secara paralel
            const realModes = MODES.filter(function (m) { return !m.derived; });

            const settled = await Promise.allSettled(
                realModes.map(function (mode) {
                    return fetchRoute(mode.profile, origin, destination);
                })
            );

            // Abaikan jika pengguna sudah mengubah input
            if (currentRequest !== requestId) return;

            realModes.forEach(function (mode, index) {
                results[mode.key] =
                    settled[index].status === 'fulfilled' ? settled[index].value : null;
            });

            // Moda perkiraan berdasarkan rute mobil
            const car = results.driving;

            if (car) {
                results.motor = {
                    distance: car.distance,
                    duration: car.duration * MOTOR_TIME_FACTOR,
                    geometry: car.geometry,
                    estimated: true
                };

                results.transit = {
                    distance: car.distance,
                    duration:
                        (car.distance / 1000 / TRANSIT_AVG_SPEED_KMH) * 3600 +
                        TRANSIT_OVERHEAD_MIN * 60,
                    geometry: car.geometry,
                    estimated: true
                };
            } else {
                results.motor = null;
                results.transit = null;
            }

            if (!getBestKey()) {
                renderModeList(false);
                statusElement.textContent =
                    'Rute gagal ditemukan. Lokasi mungkin tidak terhubung lewat jalan darat, atau periksa koneksi Anda.';
                return;
            }

            selectMode('best');
        }

        document.getElementById('find-route').addEventListener('click', findRoute);


        // =====================================
        // 11. MENGATUR ULANG PETA
        // =====================================

        document.getElementById('reset-route').addEventListener('click', function () {

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

            resetResults();

            originLabel.textContent = 'Belum ditentukan';
            originCoordinates.textContent = 'Klik peta untuk memilih lokasi awal.';

            statusElement.textContent =
                'Tentukan lokasi awal dan bandara tujuan untuk melihat rute.';

            map.setView([3, 115], 5);
        });


        // =====================================
        // 12. MEMBACA PARAMETER DARI DASHBOARD
        // =====================================

        const params = new URLSearchParams(window.location.search);

        const originLat = Number(params.get('origin_lat'));
        const originLng = Number(params.get('origin_lng'));
        const destinationLat = Number(params.get('destination_lat'));
        const destinationLng = Number(params.get('destination_lng'));
        const airportId = params.get('airport_id');

        if (
            params.has('origin_lat') && params.has('origin_lng') &&
            Number.isFinite(originLat) && Number.isFinite(originLng) &&
            Math.abs(originLat) <= 90 && Math.abs(originLng) <= 180
        ) {
            setOrigin(originLat, originLng);
        }

        if (
            params.has('destination_lat') && params.has('destination_lng') &&
            Number.isFinite(destinationLat) && Number.isFinite(destinationLng) &&
            Math.abs(destinationLat) <= 90 && Math.abs(destinationLng) <= 180
        ) {

            const selectedAirport = airportId
                ? airportData.find(function (a) { return String(a.id) === String(airportId); })
                : null;

            if (selectedAirport) {
                selectAirport(selectedAirport);
            } else {
                // Fallback: pakai koordinat & nama dari URL
                const airportName = params.get('airport_name') || 'Bandara tujuan';

                destination = L.latLng(destinationLat, destinationLng);

                destinationMarker = L.marker(destination).addTo(map).bindPopup(airportName);

                airportSearch.value = airportName;
                selectedAirportName.textContent = airportName;
                airportSelected.classList.remove('hidden');
            }

            // Jika dibuka dari tombol rekomendasi, langsung hitung rute.
            if (origin && destination) {
                findRoute();
            }
        }

    });
    </script>

</body>
</html>