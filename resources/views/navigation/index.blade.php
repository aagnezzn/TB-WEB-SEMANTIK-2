<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ __('messages.nav_page_title') }}</title>

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
        body::before {
    content: "";
    position: fixed;
    inset: 0;
    z-index: -1;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='none' stroke='%237FA8D6' stroke-width='1.4'%3E%3Cellipse cx='40' cy='20' rx='9' ry='18'/%3E%3Cellipse cx='40' cy='60' rx='9' ry='18'/%3E%3Cellipse cx='20' cy='40' rx='18' ry='9'/%3E%3Cellipse cx='60' cy='40' rx='18' ry='9'/%3E%3C/g%3E%3Cg fill='%23E0A93B'%3E%3Ccircle cx='40' cy='40' r='3'/%3E%3Ccircle cx='0' cy='0' r='2'/%3E%3Ccircle cx='80' cy='0' r='2'/%3E%3Ccircle cx='0' cy='80' r='2'/%3E%3Ccircle cx='80' cy='80' r='2'/%3E%3C/g%3E%3C/svg%3E");
    background-size: 80px 80px;
    opacity: 0.35;
    /* pola jelas di atas, memudar ke bawah supaya teks tetap nyaman dibaca */
    -webkit-mask-image: linear-gradient(to bottom, #000 0%, rgba(0,0,0,.25) 100%);
    mask-image: linear-gradient(to bottom, #000 0%, rgba(0,0,0,.25) 100%);
}
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 text-slate-800">

    @include('layouts.navbar')

    <main class="max-w-[1600px] mx-auto px-4 py-6">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-3xl md:text-4xl font-bold text-[#7FA8D6]">{{ __('messages.nav_heading') }}</h1>
            <p class="text-slate-600 mt-2">{{ __('messages.nav_sub') }}</p>
        </div>

        <!-- LAYOUT UTAMA -->
        <div class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-5 items-start">

            <!-- PANEL KIRI -->
            <aside class="bg-white/90 backdrop-blur-md rounded-3xl border-2 border-white shadow-xl p-5 h-fit">

                <h2 class="text-xl font-bold text-[#7FA8D6] mb-5">{{ __('messages.nav_route_settings') }}</h2>

                <!-- LOKASI AWAL -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold mb-2">{{ __('messages.nav_origin_location') }}</label>
                    <div class="bg-[#A8C6E7]/20 border border-[#A8C6E7] rounded-xl p-3">
                        <p id="origin-label" class="text-sm text-slate-600">{{ __('messages.nav_not_specified') }}</p>
                        <p id="origin-coordinates" class="text-xs text-slate-400 mt-1">
                            {{ __('messages.nav_click_map_origin') }}
                        </p>
                    </div>
                </div>

                <!-- PENCARIAN BANDARA TUJUAN -->
                <div class="mb-5 relative">
                    <label for="airport-search" class="block text-sm font-semibold mb-2">{{ __('messages.nav_destination_airport') }}</label>

                    <div class="relative">
                        <input type="text" id="airport-search" autocomplete="off"
                            placeholder="{{ __('messages.nav_search_placeholder') }}"
                            class="w-full p-3 pr-10 rounded-xl border border-[#A8C6E7] bg-white focus:outline-none focus:ring-2 focus:ring-[#7FA8D6]">
                        <span class="absolute right-4 top-3.5 text-[#7FA8D6]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </span>
                    </div>

                    <div id="airport-results"
                        class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-[#A8C6E7] rounded-xl shadow-xl max-h-64 overflow-y-auto z-[1000]">
                    </div>

                    <div id="airport-selected" class="hidden mt-3 p-3 rounded-xl bg-[#A8C6E7]/20 border border-[#A8C6E7]">
                        <p class="text-xs text-slate-500 mb-1">{{ __('messages.nav_selected_airport') }}</p>
                        <p id="selected-airport-name" class="font-semibold text-[#7FA8D6] text-sm"></p>
                        <button type="button" id="clear-airport" class="text-xs text-red-500 hover:text-red-700 mt-2">
                            {{ __('messages.nav_change_airport') }}
                        </button>
                    </div>
                </div>

                <!-- TOMBOL -->
                <button id="find-route"
                    class="w-full bg-[#7FA8D6] hover:bg-[#6893C7] text-white font-bold py-3 rounded-xl shadow-md transition">
                    {{ __('messages.nav_btn_find_route') }}
                </button>

                <button id="reset-route"
                    class="w-full mt-3 bg-[#FFE08A] hover:bg-[#FFD66B] text-slate-700 font-semibold py-3 rounded-xl transition">
                    {{ __('messages.nav_btn_reset_route') }}
                </button>

                <div class="border-t border-slate-200 my-6"></div>

                <!-- PERBANDINGAN MODA TRANSPORTASI -->
                <h3 class="font-bold text-slate-700 mb-3">{{ __('messages.nav_mode_comparison') }}</h3>

                <div id="mode-list" class="space-y-2">
                    <p class="text-sm text-slate-400">
                        {{ __('messages.nav_mode_comparison_empty') }}
                    </p>
                </div>

                <!-- RINGKASAN MODA TERPILIH -->
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="bg-[#A8C6E7]/20 rounded-xl p-3">
                        <p class="text-xs text-slate-500">{{ __('messages.nav_label_distance') }}</p>
                        <p id="route-distance" class="text-lg font-bold text-[#7FA8D6]">- km</p>
                    </div>
                    <div class="bg-[#FFE08A]/40 rounded-xl p-3">
                        <p class="text-xs text-slate-500">{{ __('messages.nav_label_duration') }}</p>
                        <p id="route-duration" class="text-lg font-bold text-slate-700">-</p>
                    </div>
                </div>

                <p id="route-status" class="text-sm text-slate-500 mt-4">
                    {{ __('messages.nav_prompt_set_locations') }}
                </p>

                <a id="transit-link" href="#" target="_blank" rel="noopener"
                class="hidden mt-3 text-sm font-semibold text-[#7FA8D6] hover:underline items-center gap-1">
                    <span>{{ __('messages.nav_transit_google_maps') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M7 7h10v10"/><path d="M7 17 17 7"/>
                    </svg>
                    </a>

                <p class="text-xs text-slate-400 mt-5">
                    {!! __('messages.nav_disclaimer') !!}
                </p>

            </aside>

            <!-- PETA KANAN -->
            <section class="bg-white/90 backdrop-blur-md rounded-3xl border-2 border-white shadow-xl p-3 lg:sticky lg:top-4 self-start">

                <div class="flex justify-between items-center px-2 py-3">
                    <div>
                        <h2 class="font-bold text-[#7FA8D6]">{{ __('messages.nav_map_heading') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('messages.nav_map_sub') }}</p>
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


        const navI18n = {
            min: @json(__('messages.nav_unit_min')),
            hr: @json(__('messages.nav_unit_hr')),
            d: @json(__('messages.nav_unit_d')),
            estimated: @json(__('messages.nav_estimated')),
            notAvailable: @json(__('messages.nav_not_available')),
            fastest: @json(__('messages.nav_fastest')),
            bestModePrefix: @json(__('messages.nav_best_mode_prefix')),
            modePrefix: @json(__('messages.nav_mode_prefix')),
            safeTrip: @json(__('messages.nav_safe_trip')),
            emptyComparison: @json(__('messages.nav_mode_comparison_empty')),
            originPopup: @json(__('messages.nav_origin_location')),
            originSelected: @json(__('messages.nav_origin_selected')),
            originUpdated: @json(__('messages.nav_origin_updated')),
            originSet: @json(__('messages.nav_origin_set')),
            noAirports: @json(__('messages.nav_no_airports')),
            invalidCoords: @json(__('messages.nav_invalid_coords')),
            destinationSelected: @json(__('messages.nav_destination_selected')),
            searchNewDestination: @json(__('messages.nav_search_new_destination')),
            clickMapPrompt: @json(__('messages.nav_click_map_prompt')),
            selectDestinationPrompt: @json(__('messages.nav_select_destination_prompt')),
            calculating: @json(__('messages.nav_calculating')),
            routeFailed: @json(__('messages.nav_route_failed')),
            notSpecified: @json(__('messages.nav_not_specified')),
            clickMapOrigin: @json(__('messages.nav_click_map_origin')),
            promptSetLocations: @json(__('messages.nav_prompt_set_locations')),
            destinationFallback: @json(__('messages.nav_destination_fallback'))
        };

        // =====================================
        // 3. KONFIGURASI MODA TRANSPORTASI
        // =====================================

        // Layanan routing OSRM (FOSSGIS) dengan profil berbeda.
        const ROUTING_BASE = 'https://routing.openstreetmap.de/routed-';

        const MODES = [
            { key: 'driving', label: @json(__('messages.nav_mode_driving')), icon: 'car', profile: 'car', color: '#4285F4' },
            { key: 'motor',   label: @json(__('messages.nav_mode_motor')),   icon: 'motor', derived: true, color: '#F59E0B' },
            { key: 'transit', label: @json(__('messages.nav_mode_transit')), icon: 'bus',   derived: true, color: '#0F9D58' },
            { key: 'walking', label: @json(__('messages.nav_mode_walking')), icon: 'walk',  profile: 'foot', color: '#7C3AED', dashed: true },
            { key: 'cycling', label: @json(__('messages.nav_mode_cycling')), icon: 'bike',  profile: 'bike', color: '#E11D48' }
        ];

        // Parameter perkiraan (silakan disesuaikan)
        const MOTOR_TIME_FACTOR = 0.85;     // motor ~15% lebih cepat dari mobil
        const TRANSIT_AVG_SPEED_KMH = 25;   // kecepatan rata-rata angkutan umum
        const TRANSIT_OVERHEAD_MIN = 15;    // waktu tunggu + berjalan ke halte

        const BEST_MODE = { key: 'best', label: @json(__('messages.nav_mode_best')), icon: 'star', color: '#F59E0B' };

        const ICONS = {
        car:   '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
        motor: '<circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M9 17.5h5l2-6h-4L10.5 8.5H7"/><path d="M15 8h3l.5 3"/>',
        bus:   '<path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/>',
        walk:  '<path d="M4 16v-2.38C4 11.5 2.97 10.5 3 8c.03-2.72 1.49-6 4.5-6C9.37 2 10 3.8 10 5.5c0 3.11-2 5.66-2 8.68V16a2 2 0 1 1-4 0Z"/><path d="M20 20v-2.38c0-2.12 1.03-3.12 1-5.62-.03-2.72-1.49-6-4.5-6C14.63 6 14 7.8 14 9.5c0 3.11 2 5.66 2 8.68V20a2 2 0 1 0 4 0Z"/><path d="M16 17h4"/><path d="M4 13h4"/>',
        bike:  '<circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/>',
        star:  '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'
        };

function svgIcon(name, sizeClass) {
    return '<svg class="' + sizeClass + '" fill="none" stroke="currentColor" stroke-width="1.8" ' +
           'stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' +
           ICONS[name] + '</svg>';
}


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
                return totalMinutes + ' ' + navI18n.min;
            }

            const days = Math.floor(totalMinutes / 1440);
            const hours = Math.floor((totalMinutes % 1440) / 60);
            const minutes = totalMinutes % 60;

            if (days > 0) {
                return days + ' ' + navI18n.d + ' ' + hours + ' ' + navI18n.hr;
            }

            return hours + ' ' + navI18n.hr + (minutes ? ' ' + minutes + ' ' + navI18n.min : '');
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
            icon.className = 'w-10 h-10 shrink-0 flex items-center justify-center rounded-lg';
            icon.style.color = mode.color;
            icon.style.backgroundColor = mode.color + '1A'; // latar tipis, warna sama dengan garis rute
            icon.innerHTML = svgIcon(mode.icon, 'w-5 h-5');

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
                sub.textContent = formatDistance(data.distance) + (data.estimated ? ' • ' + navI18n.estimated : '');
            } else {
                sub.textContent = options.loading ? '' : navI18n.notAvailable;
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
                        ? navI18n.fastest + ' ' + getMode(bestKey).label + ' • ' + formatDistance(bestData.distance)
                        : (isLoading ? '' : navI18n.notAvailable)
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
                line.bindTooltip(mode.label + ' • ' + formatDuration(data.duration), { sticky: true });
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
                (selectedMode === 'best' ? navI18n.bestModePrefix : navI18n.modePrefix) + mode.label +
                (data.estimated ? ' (' + navI18n.estimated + '). ' : '. ') +
                navI18n.safeTrip;
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
                '<p class="text-sm text-slate-400">' + navI18n.emptyComparison + '</p>';
        }


        // =====================================
        // 8. MENGATUR LOKASI AWAL
        // =====================================

        function setOrigin(lat, lng) {

            origin = L.latLng(lat, lng);

            if (originMarker) {
                map.removeLayer(originMarker);
            }

            originMarker = L.marker(origin).addTo(map).bindPopup(navI18n.originPopup).openPopup();

            originLabel.textContent = navI18n.originSelected;
            originCoordinates.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);

            resetResults();

            statusElement.textContent = destination
                ? navI18n.originUpdated
                : navI18n.originSet;
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
                    '<div class="p-4 text-sm text-slate-500 text-center">' + navI18n.noAirports + '</div>';
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
                statusElement.textContent = navI18n.invalidCoords;
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
                navI18n.destinationSelected;
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

            statusElement.textContent = navI18n.searchNewDestination;
        });


        // =====================================
        // 10. MENCARI RUTE UNTUK SEMUA MODA
        // =====================================

        async function findRoute() {

            if (!origin) {
                statusElement.textContent = navI18n.clickMapPrompt;
                return;
            }

            if (!destination) {
                statusElement.textContent = navI18n.selectDestinationPrompt;
                return;
            }

            const currentRequest = ++requestId;

            results = {};
            selectedMode = 'best';
            routeLayer.clearLayers();

            distanceElement.textContent = '- km';
            durationElement.textContent = '-';
            transitLink.classList.add('hidden');

            statusElement.textContent = navI18n.calculating;
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
                    navI18n.routeFailed;
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

            originLabel.textContent = navI18n.notSpecified;
            originCoordinates.textContent = navI18n.clickMapOrigin;

            statusElement.textContent =
                navI18n.promptSetLocations;

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
                const airportName = params.get('airport_name') || navI18n.destinationFallback;

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