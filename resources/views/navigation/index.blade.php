<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ __('messages.nav_page_title') }}</title>
    @include('layouts.og', ['ogTitle' => __('messages.nav_page_title'), 'ogDescription' => 'AeroSearch ASEAN: data bandara semantik.'])

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

        .dir-step { transition: background .15s ease; }
        .dir-step:hover { background: rgba(168, 198, 231, .25); }
        .route-chip.active { background: #7FA8D6; color: #fff; border-color: #7FA8D6; }
        .leaflet-marker-draggable { cursor: grab; }

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
            <aside class="bg-white/90 backdrop-blur-md rounded-3xl border-2 border-white shadow-xl p-5 h-fit lg:sticky lg:top-4 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">

                <h2 class="text-xl font-bold text-[#7FA8D6] mb-5">{{ __('messages.nav_route_settings') }}</h2>

                <!-- LOKASI AWAL -->
                <div class="mb-5 relative">
                    <label for="origin-search" class="block text-sm font-semibold mb-2">{{ __('messages.nav_origin_location') }}</label>

                    <div class="flex gap-2">
                        <input type="text" id="origin-search" autocomplete="off"
                            placeholder="{{ __('messages.nav_origin_search_placeholder') }}"
                            class="flex-1 min-w-0 p-3 rounded-xl border border-[#A8C6E7] bg-white focus:outline-none focus:ring-2 focus:ring-[#7FA8D6]">
                        <button type="button" id="use-my-location"
                            title="{{ __('messages.nav_use_my_location') }}" aria-label="{{ __('messages.nav_use_my_location') }}"
                            class="shrink-0 w-12 rounded-xl border border-[#A8C6E7] bg-white text-[#4285F4] hover:bg-[#A8C6E7]/20 flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="8"/>
                                <path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
                            </svg>
                        </button>
                    </div>

                    <div id="origin-results"
                        class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-[#A8C6E7] rounded-xl shadow-xl max-h-64 overflow-y-auto z-[1000]">
                    </div>

                    <div class="bg-[#A8C6E7]/20 border border-[#A8C6E7] rounded-xl p-3 mt-3">
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
                        <p id="route-arrival" class="text-xs text-slate-500 mt-0.5"></p>
                    </div>
                </div>

                <p id="route-status" class="text-sm text-slate-500 mt-4">
                    {{ __('messages.nav_prompt_set_locations') }}
                </p>

                <!-- PILIHAN RUTE ALTERNATIF -->
                <div id="route-alts-wrap" class="hidden mt-4">
                    <p class="text-xs font-semibold text-slate-500 mb-2">{{ __('messages.nav_alt_routes') }}</p>
                    <div id="route-alts" class="flex flex-wrap gap-2"></div>
                </div>

                <!-- PETUNJUK ARAH BELOK DEMI BELOK -->
                <div id="directions-wrap" class="hidden mt-5">
                    <h3 class="font-bold text-slate-700 mb-2">{{ __('messages.nav_directions') }}</h3>
                    <ol id="directions-list" class="space-y-1 max-h-80 overflow-y-auto pr-1"></ol>
                </div>

                <button type="button" id="copy-link"
                    class="hidden mt-4 w-full text-sm font-semibold text-[#7FA8D6] border border-[#A8C6E7] rounded-xl py-2 hover:bg-[#A8C6E7]/20 transition">
                    {{ __('messages.nav_copy_link') }}
                </button>

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

        const originSearch = document.getElementById('origin-search');
        const originResults = document.getElementById('origin-results');
        const useMyLocationButton = document.getElementById('use-my-location');
        const arrivalElement = document.getElementById('route-arrival');
        const altsWrap = document.getElementById('route-alts-wrap');
        const altsElement = document.getElementById('route-alts');
        const directionsWrap = document.getElementById('directions-wrap');
        const directionsList = document.getElementById('directions-list');
        const copyLinkButton = document.getElementById('copy-link');


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
            destinationFallback: @json(__('messages.nav_destination_fallback')),
            myLocation: @json(__('messages.nav_my_location')),
            locating: @json(__('messages.nav_locating')),
            geolocationFailed: @json(__('messages.nav_geolocation_failed')),
            geocodeFailed: @json(__('messages.nav_geocode_failed')),
            noPlaces: @json(__('messages.nav_no_places')),
            arriveAt: @json(__('messages.nav_arrive_at')),
            routeN: @json(__('messages.nav_route_n')),
            linkCopied: @json(__('messages.nav_link_copied')),
            copyLink: @json(__('messages.nav_copy_link')),
            dirStart: @json(__('messages.nav_dir_start')),
            dirStartOn: @json(__('messages.nav_dir_start_on')),
            dirArrive: @json(__('messages.nav_dir_arrive')),
            dirContinue: @json(__('messages.nav_dir_continue')),
            dirOnto: @json(__('messages.nav_dir_onto')),
            dirRoundabout: @json(__('messages.nav_dir_roundabout')),
            dirExitRoundabout: @json(__('messages.nav_dir_exit_roundabout')),
            dirFerry: @json(__('messages.nav_dir_ferry')),
            dirMod: {
                'left': @json(__('messages.nav_dir_left')),
                'right': @json(__('messages.nav_dir_right')),
                'slight left': @json(__('messages.nav_dir_slight_left')),
                'slight right': @json(__('messages.nav_dir_slight_right')),
                'sharp left': @json(__('messages.nav_dir_sharp_left')),
                'sharp right': @json(__('messages.nav_dir_sharp_right')),
                'straight': @json(__('messages.nav_dir_straight')),
                'uturn': @json(__('messages.nav_dir_uturn'))
            }
        };

        const PHOTON_URL = 'https://photon.komoot.io/';
        const ASEAN_BBOX = '90,-13,142,28';   // minLon,minLat,maxLon,maxLat

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
        let routeIndex = 0;            // rute alternatif yang sedang dipilih
        let destinationAirport = null; // objek bandara tujuan (untuk tautan yang bisa dibagikan)
        let initializing = true;       // hindari hitung rute berulang saat membaca parameter URL

        const routeLayer = L.layerGroup().addTo(map);
        const stepLayer = L.layerGroup().addTo(map);


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

        function normalizeRoute(route) {
            const leg = route.legs && route.legs[0] ? route.legs[0] : null;

            return {
                distance: route.distance,
                duration: route.duration,
                geometry: route.geometry,
                steps: leg ? leg.steps : []
            };
        }

        async function fetchRoute(profile, from, to) {

            const url = ROUTING_BASE + profile +
                '/route/v1/driving/' +
                from.lng + ',' + from.lat + ';' + to.lng + ',' + to.lat +
                '?overview=full&geometries=geojson&steps=true' +
                (profile === 'car' ? '&alternatives=true' : '');

            const controller = new AbortController();
            const timer = setTimeout(function () { controller.abort(); }, 25000);

            try {
                const response = await fetch(url, { signal: controller.signal });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const data = await response.json();

                if (data.code !== 'Ok' || !data.routes || !data.routes.length) {
                    throw new Error(data.code || 'NoRoute');
                }

                const routes = data.routes.slice(0, 3).map(normalizeRoute);

                return Object.assign({}, routes[0], { routes: routes });

            } finally {
                clearTimeout(timer);
            }
        }

        // Moda perkiraan (motor, transportasi umum) diturunkan dari rute mobil.
        function deriveFromCar(car, durationFn, extra) {
            const routes = car.routes.map(function (r) {
                return Object.assign({}, r, { duration: durationFn(r) }, extra || {});
            });

            return Object.assign({}, routes[0], { routes: routes, estimated: true });
        }

        function currentRoute(key) {
            const data = results[key];
            if (!data) return null;
            return data.routes[Math.min(routeIndex, data.routes.length - 1)];
        }

        function formatArrival(seconds) {
            const arrival = new Date(Date.now() + seconds * 1000);
            return navI18n.arriveAt + ' ' +
                arrival.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function formatStepDistance(meters) {
            if (meters < 1000) {
                return Math.max(10, Math.round(meters / 10) * 10) + ' m';
            }
            return formatDistance(meters);
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

        function drawRoutes(fit) {

            routeLayer.clearLayers();
            stepLayer.clearLayers();

            const activeKey = resolveActiveKey();
            if (!activeKey || !results[activeKey]) return;

            // Rute moda lain (abu-abu), hanya moda dengan rute asli
            MODES.forEach(function (mode) {

                const data = results[mode.key];

                if (!data || mode.derived || mode.key === activeKey) return;

                const line = L.geoJSON(data.geometry, {
                    style: { color: '#94A3B8', weight: 5, opacity: 0.55 }
                });

                line.on('click', function () { selectMode(mode.key); });
                line.bindTooltip(mode.label + ' • ' + formatDuration(data.duration), { sticky: true });
                line.addTo(routeLayer);
            });

            const activeMode = getMode(activeKey);
            const activeData = results[activeKey];
            const activeIndex = Math.min(routeIndex, activeData.routes.length - 1);

            // Rute alternatif pada moda aktif
            activeData.routes.forEach(function (route, index) {

                if (index === activeIndex) return;

                const line = L.geoJSON(route.geometry, {
                    style: { color: '#94A3B8', weight: 7, opacity: 0.85 }
                });

                line.on('click', function () { selectRouteIndex(index); });
                line.bindTooltip(
                    navI18n.routeN + ' ' + (index + 1) + ' • ' + formatDuration(route.duration),
                    { sticky: true }
                );
                line.addTo(routeLayer);
            });

            // Rute aktif
            const activeLine = L.geoJSON(activeData.routes[activeIndex].geometry, {
                style: {
                    color: activeMode.color,
                    weight: activeMode.dashed ? 6 : 8,
                    opacity: 0.95,
                    dashArray: activeMode.dashed ? '1, 12' : null,
                    lineCap: 'round'
                }
            }).addTo(routeLayer);

            if (fit !== false) {
                map.fitBounds(activeLine.getBounds(), { padding: [50, 50] });
            }
        }

        // ---- Petunjuk arah belok demi belok ----

        const ARROW_ROTATION = {
            'straight': 0, 'slight right': 45, 'right': 90, 'sharp right': 135,
            'uturn': 180, 'sharp left': -135, 'left': -90, 'slight left': -45
        };

        function stepText(step) {
            const maneuver = step.maneuver || {};
            const road = step.name || '';
            const onto = road ? ' ' + navI18n.dirOnto.replace(':road', road) : '';

            if (step.mode === 'ferry' && maneuver.type !== 'arrive') {
                return navI18n.dirFerry + (road ? ' (' + road + ')' : '');
            }

            switch (maneuver.type) {
                case 'depart':
                    return road ? navI18n.dirStartOn.replace(':road', road) : navI18n.dirStart;
                case 'arrive':
                    return navI18n.dirArrive;
                case 'roundabout':
                case 'rotary':
                case 'roundabout turn':
                    return navI18n.dirRoundabout.replace(':n', maneuver.exit || '') + onto;
                case 'exit roundabout':
                case 'exit rotary':
                    return navI18n.dirExitRoundabout + onto;
                default:
                    return (navI18n.dirMod[maneuver.modifier] || navI18n.dirContinue) + onto;
            }
        }

        function stepIcon(step) {
            const maneuver = step.maneuver || {};

            if (maneuver.type === 'depart') return '<span class="text-[#4285F4]">&#9679;</span>';
            if (maneuver.type === 'arrive') return '<span class="text-red-500">&#9873;</span>';
            if (/roundabout|rotary/.test(maneuver.type || '')) return '<span>&#10227;</span>';

            const rotation = ARROW_ROTATION[maneuver.modifier] || 0;

            return '<svg class="w-4 h-4" style="transform: rotate(' + rotation + 'deg)" fill="none" ' +
                'stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" ' +
                'viewBox="0 0 24 24"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg>';
        }

        function renderDirections(route, key) {

            directionsList.innerHTML = '';

            // Transportasi umum hanya perkiraan, tidak punya petunjuk belokan
            if (!route || !route.steps || !route.steps.length || key === 'transit') {
                directionsWrap.classList.add('hidden');
                return;
            }

            route.steps.forEach(function (step) {

                const maneuver = step.maneuver || {};
                const item = document.createElement('li');
                const button = document.createElement('button');

                button.type = 'button';
                button.className = 'dir-step w-full flex items-start gap-3 text-left p-2 rounded-lg';

                const icon = document.createElement('span');
                icon.className = 'w-7 h-7 shrink-0 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 text-sm';
                icon.innerHTML = stepIcon(step);

                const text = document.createElement('span');
                text.className = 'flex-1 min-w-0 text-sm text-slate-700';
                text.textContent = stepText(step);

                button.appendChild(icon);
                button.appendChild(text);

                if (maneuver.type !== 'arrive' && step.distance > 0) {
                    const distance = document.createElement('span');
                    distance.className = 'shrink-0 text-xs text-slate-400 mt-1';
                    distance.textContent = formatStepDistance(step.distance);
                    button.appendChild(distance);
                }

                button.addEventListener('click', function () {
                    if (!maneuver.location) return;

                    const point = L.latLng(maneuver.location[1], maneuver.location[0]);

                    stepLayer.clearLayers();
                    L.circleMarker(point, {
                        radius: 9, color: '#fff', weight: 3, fillColor: '#4285F4', fillOpacity: 1
                    }).addTo(stepLayer);

                    map.setView(point, 17);
                });

                item.appendChild(button);
                directionsList.appendChild(item);
            });

            directionsWrap.classList.remove('hidden');
        }

        function renderAlternatives(activeKey) {

            altsElement.innerHTML = '';

            const data = results[activeKey];

            if (!data || data.routes.length < 2) {
                altsWrap.classList.add('hidden');
                return;
            }

            const activeIndex = Math.min(routeIndex, data.routes.length - 1);

            data.routes.forEach(function (route, index) {

                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'route-chip text-xs font-semibold px-3 py-1.5 rounded-full border border-slate-300 bg-white ' +
                    'hover:border-[#7FA8D6] transition' + (index === activeIndex ? ' active' : '');
                chip.textContent = navI18n.routeN + ' ' + (index + 1) + ' • ' +
                    formatDuration(route.duration) + ' • ' + formatDistance(route.distance);

                chip.addEventListener('click', function () { selectRouteIndex(index); });
                altsElement.appendChild(chip);
            });

            altsWrap.classList.remove('hidden');
        }

        function updateSummary() {

            const activeKey = resolveActiveKey();
            const data = activeKey ? results[activeKey] : null;
            const route = activeKey ? currentRoute(activeKey) : null;

            if (!data || !route) {
                distanceElement.textContent = '- km';
                durationElement.textContent = '-';
                arrivalElement.textContent = '';
                transitLink.classList.add('hidden');
                altsWrap.classList.add('hidden');
                directionsWrap.classList.add('hidden');
                return;
            }

            distanceElement.textContent = formatDistance(route.distance);
            durationElement.textContent = formatDuration(route.duration);
            arrivalElement.textContent = formatArrival(route.duration);

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

            renderAlternatives(activeKey);
            renderDirections(route, activeKey);
        }

        function selectMode(key) {
            selectedMode = key;
            routeIndex = 0;
            renderModeList(false);
            drawRoutes();
            updateSummary();
        }

        function selectRouteIndex(index) {
            routeIndex = index;
            drawRoutes(false);
            updateSummary();
        }

        function resetResults() {
            requestId++;   // batalkan permintaan yang sedang berjalan
            results = {};
            selectedMode = 'best';
            routeIndex = 0;
            routeLayer.clearLayers();
            stepLayer.clearLayers();
            distanceElement.textContent = '- km';
            durationElement.textContent = '-';
            arrivalElement.textContent = '';
            transitLink.classList.add('hidden');
            altsWrap.classList.add('hidden');
            directionsWrap.classList.add('hidden');
            modeListElement.innerHTML =
                '<p class="text-sm text-slate-400">' + navI18n.emptyComparison + '</p>';
        }


        // =====================================
        // 8. MENGATUR LOKASI AWAL
        // =====================================

        function setOrigin(lat, lng, label) {

            origin = L.latLng(lat, lng);

            if (originMarker) {
                map.removeLayer(originMarker);
            }

            originMarker = L.marker(origin, { draggable: true }).addTo(map).bindPopup(navI18n.originPopup);

            originMarker.on('dragend', function () {
                const position = originMarker.getLatLng();
                originSearch.value = '';
                setOrigin(position.lat, position.lng);
            });

            originLabel.textContent = label || navI18n.originSelected;
            originCoordinates.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);

            resetResults();

            statusElement.textContent = destination
                ? navI18n.originUpdated
                : navI18n.originSet;

            // Nama tempat dari koordinat (geocoding balik), kecuali label sudah diberikan
            if (!label) {
                reverseGeocode(lat, lng);
            }

            // Hitung rute otomatis bila kedua titik sudah ada
            if (!initializing && destination) {
                findRoute();
            }
        }

        async function reverseGeocode(lat, lng) {
            try {
                const response = await fetch(
                    PHOTON_URL + 'reverse?lat=' + lat + '&lon=' + lng + '&lang=en'
                );
                const data = await response.json();
                const feature = data.features && data.features[0];

                // Abaikan jika titik awal sudah berpindah
                if (!feature || !origin || origin.lat !== lat || origin.lng !== lng) return;

                const text = placeLabel(feature.properties);

                if (text) {
                    originLabel.textContent = text;
                }
            } catch (error) {
                // Nama tempat opsional, abaikan kegagalan
            }
        }

        function placeLabel(properties) {
            return [
                properties.name, properties.street, properties.district,
                properties.city, properties.state, properties.country
            ].filter(function (part, index, all) {
                return part && all.indexOf(part) === index;
            }).join(', ');
        }

        map.on('click', function (event) {
            originSearch.value = '';
            setOrigin(event.latlng.lat, event.latlng.lng);
        });


        // ---- Pencarian alamat / tempat (geocoding) ----

        let geocodeTimer = null;
        let geocodeController = null;

        function renderPlaceMessage(text) {
            originResults.innerHTML = '<div class="p-4 text-sm text-slate-500 text-center"></div>';
            originResults.firstChild.textContent = text;
            originResults.classList.remove('hidden');
        }

        async function searchPlaces(query) {

            if (geocodeController) {
                geocodeController.abort();
            }

            geocodeController = new AbortController();

            try {
                const response = await fetch(
                    PHOTON_URL + 'api/?q=' + encodeURIComponent(query) +
                    '&limit=6&lang=en&bbox=' + ASEAN_BBOX,
                    { signal: geocodeController.signal }
                );

                if (!response.ok) throw new Error('HTTP ' + response.status);

                const data = await response.json();
                const features = data.features || [];

                if (!features.length) {
                    renderPlaceMessage(navI18n.noPlaces);
                    return;
                }

                originResults.innerHTML = '';

                features.forEach(function (feature) {

                    const properties = feature.properties;
                    const label = placeLabel(properties);

                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'w-full text-left p-3 border-b border-slate-100 hover:bg-[#A8C6E7]/20 transition';

                    const title = document.createElement('p');
                    title.className = 'font-semibold text-sm text-slate-800';
                    title.textContent = properties.name || properties.street || label;

                    const subtitle = document.createElement('p');
                    subtitle.className = 'text-xs text-slate-500 mt-1';
                    subtitle.textContent = label;

                    item.appendChild(title);
                    item.appendChild(subtitle);

                    item.addEventListener('click', function () {
                        const lng = feature.geometry.coordinates[0];
                        const lat = feature.geometry.coordinates[1];

                        originSearch.value = label;
                        originResults.classList.add('hidden');
                        setOrigin(lat, lng, label);

                        if (!destination) {
                            map.setView(origin, 13);
                        }
                    });

                    originResults.appendChild(item);
                });

                originResults.classList.remove('hidden');

            } catch (error) {
                if (error.name === 'AbortError') return;
                renderPlaceMessage(navI18n.geocodeFailed);
            }
        }

        originSearch.addEventListener('input', function () {

            clearTimeout(geocodeTimer);

            const query = this.value.trim();

            if (query.length < 3) {
                originResults.classList.add('hidden');
                return;
            }

            geocodeTimer = setTimeout(function () { searchPlaces(query); }, 350);
        });

        document.addEventListener('click', function (event) {
            if (!originSearch.contains(event.target) && !originResults.contains(event.target)) {
                originResults.classList.add('hidden');
            }
        });

        // ---- Lokasi saya (geolocation browser) ----

        useMyLocationButton.addEventListener('click', function () {

            if (!navigator.geolocation) {
                statusElement.textContent = navI18n.geolocationFailed;
                return;
            }

            statusElement.textContent = navI18n.locating;

            navigator.geolocation.getCurrentPosition(function (position) {

                originSearch.value = navI18n.myLocation;
                setOrigin(position.coords.latitude, position.coords.longitude, navI18n.myLocation);

                if (!destination) {
                    map.setView(origin, 13);
                }

            }, function () {
                statusElement.textContent = navI18n.geolocationFailed;
            }, { enableHighAccuracy: true, timeout: 10000 });
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
            destinationAirport = airport;

            if (destinationMarker) {
                map.removeLayer(destinationMarker);
            }

            destinationMarker = L.marker(destination).addTo(map).bindPopup(airport.name);

            airportSearch.value = airport.name;
            selectedAirportName.textContent = airport.name + ' (' + (airport.iata || '-') + ')';

            airportSelected.classList.remove('hidden');
            airportResults.classList.add('hidden');

            resetResults();

            statusElement.textContent =
                navI18n.destinationSelected;

            // Hitung rute otomatis bila lokasi awal sudah ada
            if (!initializing && origin) {
                findRoute();
            } else if (!origin) {
                map.setView(destination, 10);
            }
        }

        airportSearch.addEventListener('input', function () {

            destination = null;
            destinationAirport = null;

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
            destinationAirport = null;

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
            routeIndex = 0;
            routeLayer.clearLayers();
            stepLayer.clearLayers();

            distanceElement.textContent = '- km';
            durationElement.textContent = '-';
            arrivalElement.textContent = '';
            transitLink.classList.add('hidden');
            altsWrap.classList.add('hidden');
            directionsWrap.classList.add('hidden');

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
                results.motor = deriveFromCar(car, function (r) {
                    return r.duration * MOTOR_TIME_FACTOR;
                });

                results.transit = deriveFromCar(car, function (r) {
                    return (r.distance / 1000 / TRANSIT_AVG_SPEED_KMH) * 3600 + TRANSIT_OVERHEAD_MIN * 60;
                }, { steps: [] });
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
            syncUrl();
        }

        // Simpan rute ke URL agar bisa dibagikan atau dimuat ulang
        function syncUrl() {

            if (!origin || !destination) return;

            const query = new URLSearchParams({
                origin_lat: origin.lat.toFixed(6),
                origin_lng: origin.lng.toFixed(6),
                destination_lat: destination.lat.toFixed(6),
                destination_lng: destination.lng.toFixed(6)
            });

            if (destinationAirport) {
                query.set('airport_id', destinationAirport.id);
                query.set('airport_name', destinationAirport.name);
            }

            try {
                history.replaceState(null, '', window.location.pathname + '?' + query.toString());
            } catch (error) {
                // URL tidak bisa diubah (mis. konteks terbatas), abaikan
            }

            copyLinkButton.classList.remove('hidden');
        }

        copyLinkButton.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(window.location.href);
                copyLinkButton.textContent = navI18n.linkCopied;
            } catch (error) {
                window.prompt(navI18n.copyLink, window.location.href);
            }

            setTimeout(function () { copyLinkButton.textContent = navI18n.copyLink; }, 2000);
        });

        document.getElementById('find-route').addEventListener('click', findRoute);


        // =====================================
        // 11. MENGATUR ULANG PETA
        // =====================================

        document.getElementById('reset-route').addEventListener('click', function () {

            origin = null;
            destination = null;
            destinationAirport = null;

            originSearch.value = '';
            originResults.classList.add('hidden');
            copyLinkButton.classList.add('hidden');

            try {
                history.replaceState(null, '', window.location.pathname);
            } catch (error) {
                // abaikan
            }

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
                destinationAirport = null;

                destinationMarker = L.marker(destination).addTo(map).bindPopup(airportName);

                airportSearch.value = airportName;
                selectedAirportName.textContent = airportName;
                airportSelected.classList.remove('hidden');
            }

            // Jika dibuka dari tombol rekomendasi atau tautan bersama, langsung hitung rute.
            if (origin && destination) {
                findRoute();
            }
        }

        initializing = false;

    });
    </script>

</body>
</html>