<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $airport['name'] ?? __('messages.show_page_title') }} - AeroSearch</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">
    
    <!-- Open Graph Protocol (Meta Tags Media Sosial / Web Semantik) -->
    @include('layouts.og', [
        'ogTitle' => ($airport['name'] ?? 'Airport Details') . ' - AeroSearch',
        'ogDescription' => 'Complete information on ' . ($airport['name'] ?? '') . ' in ' . ($airport['municipality'] ?? '-') . ', ' . ($airport['iso_country'] ?? '-') . '. ICAO Code: ' . ($airport['ident'] ?? '-') . '.',
        'ogType' => 'place',
        'ogImage' => $semantic['thumbnail'] ?? null,
    ])

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
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
<body class="bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 min-h-screen flex flex-col relative text-slate-800">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Detail Content dengan Anotasi RDFa (schema.org/Airport) -->
    <main class="w-full max-w-4xl mx-auto px-4 py-16 flex-grow" vocab="http://schema.org/" typeof="Airport">

        <!-- Card Utama Detail -->
        <div class="bg-white/80 backdrop-blur-md p-8 rounded-3xl border-2 border-white shadow-xl">
            <!-- Header Nama Bandara & Status Scheduled Sejajar -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider px-3.5 py-1.5 bg-[#A8C6E7]/30 text-[#7FA8D6] rounded-full mb-3 inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span property="description">{{ $airport['type'] ?? 'Airport' }}</span>
                    </span>
                    <h1 class="text-3xl font-bold text-slate-800" property="name">
                        {{ $airport['name'] ?? 'Airport Name Not Available' }}
                    </h1>
                </div>

                <!-- Status Layanan Berjadwal -->
                @if(isset($airport['scheduled_service']))
                <div class="bg-[#A8C6E7]/20 px-4 py-2.5 rounded-2xl border border-[#A8C6E7]/40 shrink-0">
                    <p class="text-xs font-medium text-slate-500 mb-0.5">{{ __('messages.show_scheduled_service') }}</p>
                    <p class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Jam -->
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @if($airport['scheduled_service'] == 'yes' || $airport['scheduled_service'] === true || $airport['scheduled_service'] == '1')
                            <span class="text-emerald-600 font-bold">{{ __('messages.show_scheduled_yes') }}</span>
                        @else
                            <span class="text-slate-500">{{ __('messages.show_scheduled_no') }}</span>
                        @endif
                    </p>
                </div>
                @endif
            </div>

            <!-- Grid Informasi Detail Lainnya -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200/60 pt-6">
                
                <!-- Lokasi / Kota (RDFa Place & PostalAddress) -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80" property="location" typeof="Place">
                    <p class="text-xs font-medium text-slate-500 mb-1">{{ __('messages.show_label_city') }}</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2" property="address" typeof="PostalAddress">
                        <!-- SVG Pin Lokasi -->
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span><span property="addressLocality">{{ $airport['municipality'] ?? '-' }}</span>, <span property="addressCountry">{{ $airport['iso_country'] ?? '-' }}</span></span>
                    </p>
                </div>

                <!-- Kode ICAO / IATA -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">{{ __('messages.show_label_codes') }}</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Pesawat -->
                        <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span><span property="icaoCode">{{ $airport['ident'] ?? '-' }}</span> / <span property="iataCode">{{ $airport['iata_code'] ?? '-' }}</span></span>
                    </p>
                </div>

                <!-- Koordinat (RDFa GeoCoordinates) -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80" property="geo" typeof="GeoCoordinates">
                    <p class="text-xs font-medium text-slate-500 mb-1">{{ __('messages.show_label_coordinates') }}</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Globe / Koordinat -->
                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><span property="latitude">{{ $airport['latitude_deg'] ?? '-' }}</span>, <span property="longitude">{{ $airport['longitude_deg'] ?? '-' }}</span></span>
                    </p>
                </div>

                <!-- Benua / Wilayah -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">{{ __('messages.show_label_region') }}</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Globe Alt -->
                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <span>{{ $airport['continent'] ?? '-' }}</span>
                    </p>
                </div>

                <!-- Situs Web Resmi (RDFa url) -->
                @if(!empty($airport['home_link']) || !empty($airport['website']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">{{ __('messages.show_label_website') }}</p>
                    <a property="url" href="{{ $airport['home_link'] ?? $airport['website'] }}" target="_blank" rel="noopener noreferrer" class="text-base font-semibold text-[#7FA8D6] hover:underline break-all flex items-center gap-2">
                        <!-- SVG Link -->
                        <svg class="w-4 h-4 text-[#7FA8D6] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        <span>{{ __('messages.show_btn_visit_website') }} &rarr;</span>
                    </a>
                </div>
                @endif

                <!-- Referensi Wikipedia -->
                @if(!empty($airport['wikipedia_link']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">{{ __('messages.show_label_wikipedia') }}</p>
                    <a href="{{ $airport['wikipedia_link'] }}" target="_blank" rel="noopener noreferrer" class="text-base font-semibold text-[#7FA8D6] hover:underline break-all flex items-center gap-2">
                        <!-- SVG Buku / Wikipedia -->
                        <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>{{ __('messages.show_btn_open_article') }} &rarr;</span>
                    </a>
                </div>
                @endif

                <!-- Kontak / Telepon (RDFa telephone) -->
                @if(!empty($airport['phone']) || !empty($airport['contact']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80 md:col-span-2">
                    <p class="text-xs font-medium text-slate-500 mb-1">{{ __('messages.show_label_contact') }}</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Telepon -->
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span property="telephone">{{ $airport['phone'] ?? $airport['contact'] }}</span>
                    </p>
                </div>
                @endif

                <!-- Data tambahan dari DBpedia (SPARQL endpoint eksternal) -->
                @if(!empty($semantic) && (!empty($semantic['abstract']) || !empty($semantic['operator']) || !empty($semantic['opened']) || !empty($semantic['thumbnail'])))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80 md:col-span-2">
                    <p class="text-xs font-medium text-slate-500 mb-2">DBpedia</p>
                    @if(!empty($semantic['thumbnail']))
                        <img src="{{ $semantic['thumbnail'] }}" alt="{{ $airport['name'] ?? '' }}" class="rounded-xl mb-3 max-h-48 object-cover" loading="lazy">
                    @endif
                    @if(!empty($semantic['operator']))
                        <p class="text-sm text-slate-700"><span class="font-semibold">Operator:</span> {{ $semantic['operator'] }}</p>
                    @endif
                    @if(!empty($semantic['opened']))
                        <p class="text-sm text-slate-700"><span class="font-semibold">Opened:</span> {{ $semantic['opened'] }}</p>
                    @endif
                    @if(!empty($semantic['abstract']))
                        <p class="text-sm text-slate-600 mt-2" property="description">{{ \Illuminate\Support\Str::limit($semantic['abstract'], 700) }}</p>
                    @endif
                    @if(!empty($semantic['dbpedia']))
                        <a href="{{ $semantic['dbpedia'] }}" property="sameAs" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-[#7FA8D6] hover:underline mt-2 inline-block">{{ $semantic['dbpedia'] }}</a>
                    @endif
                </div>
                @endif

            </div>

            <!-- Tombol Kembali di Kanan Bawah -->
            <div class="mt-8 pt-6 border-t border-slate-200/60 flex justify-end">
                <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm font-semibold bg-[#7FA8D6] hover:bg-[#6893c7] text-white px-5 py-2.5 rounded-xl transition shadow-sm gap-2">
                    <span>&larr; {{ __('messages.show_btn_back') }}</span>
                </a>
            </div>
        </div>

        <!-- Operasi Real-time: cuaca METAR + pesawat di sekitar bandara -->
        @php
            $liveIcao = strtoupper((string) ($airport['icao_code'] ?? $airport['ident'] ?? ''));
            $liveEnabled = preg_match('/^[A-Z0-9]{3,8}$/', $liveIcao) === 1
                && is_numeric($airport['latitude_deg'] ?? null)
                && is_numeric($airport['longitude_deg'] ?? null);
        @endphp

        @if($liveEnabled)
        <section id="live-ops" class="mt-8 bg-white/80 backdrop-blur-md p-6 md:p-8 rounded-3xl border-2 border-white shadow-xl">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-800">{{ __('messages.live_title') }}</h2>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        LIVE
                    </span>
                </div>
                <p id="live-updated" class="text-xs text-slate-500">{{ __('messages.live_loading') }}</p>
            </div>

            <!-- Cuaca bandara -->
            <h3 class="text-sm font-semibold text-slate-500 mb-2">{{ __('messages.live_weather') }}</h3>
            <div id="live-weather" class="mb-6">
                <p class="text-sm text-slate-400">{{ __('messages.live_loading') }}</p>
            </div>

            <!-- Lalu lintas pesawat -->
            <h3 class="text-sm font-semibold text-slate-500 mb-2">{{ __('messages.live_traffic') }}</h3>
            <div id="live-counts" class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4"></div>

            <div id="live-map" class="rounded-2xl overflow-hidden border border-white/80" style="height: 340px; z-index: 0;"></div>

            <ul id="live-aircraft" class="mt-4 divide-y divide-slate-100 text-sm"></ul>
        </section>
        @endif
    </main>

    <!-- Footer -->
    @include('layouts.footer')

    @if($liveEnabled)
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {
        const LIVE_URL = @json(route('airports.live', ['icao' => $liveIcao]));
        const AIRPORT = { lat: @json((float) $airport['latitude_deg']), lng: @json((float) $airport['longitude_deg']), name: @json($airport['name'] ?? '') };
        const REFRESH_MS = 30000;

        const t = {
            updated: @json(__('messages.live_updated')),
            unavailable: @json(__('messages.live_weather_unavailable')),
            trafficUnavailable: @json(__('messages.live_traffic_unavailable')),
            noAircraft: @json(__('messages.live_no_aircraft')),
            wind: @json(__('messages.live_wind')),
            calm: @json(__('messages.live_calm')),
            visibility: @json(__('messages.live_visibility')),
            clouds: @json(__('messages.live_clouds')),
            clear: @json(__('messages.live_clear')),
            temperature: @json(__('messages.live_temperature')),
            pressure: @json(__('messages.live_pressure')),
            observed: @json(__('messages.live_observed')),
            rawMetar: @json(__('messages.live_raw_metar')),
            ground: @json(__('messages.live_ground')),
            arriving: @json(__('messages.live_arriving')),
            departing: @json(__('messages.live_departing')),
            overflight: @json(__('messages.live_overflight')),
            total: @json(__('messages.live_total')),
            altitude: @json(__('messages.live_altitude')),
            note: @json(__('messages.live_traffic_note')),
            cat: {
                VFR: @json(__('messages.live_cat_vfr')),
                MVFR: @json(__('messages.live_cat_mvfr')),
                IFR: @json(__('messages.live_cat_ifr')),
                LIFR: @json(__('messages.live_cat_lifr'))
            }
        };

        const CAT_COLORS = { VFR: '#16a34a', MVFR: '#2563eb', IFR: '#dc2626', LIFR: '#9333ea' };
        const PHASE_COLORS = { ground: '#64748b', arriving: '#f59e0b', departing: '#0ea5e9', overflight: '#7FA8D6' };
        const COVER = { FEW: 'Few', SCT: 'Scattered', BKN: 'Broken', OVC: 'Overcast', CLR: 'Clear', SKC: 'Clear', NSC: 'Clear', CAVOK: 'CAVOK', VV: 'Vertical vis.' };

        const weatherBox = document.getElementById('live-weather');
        const countsBox = document.getElementById('live-counts');
        const listBox = document.getElementById('live-aircraft');
        const noteBox = document.getElementById('live-traffic-note');
        const updatedBox = document.getElementById('live-updated');

        // ---- Peta pesawat ----
        const map = L.map('live-map', { scrollWheelZoom: false }).setView([AIRPORT.lat, AIRPORT.lng], 9);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors', maxZoom: 18
        }).addTo(map);

        L.marker([AIRPORT.lat, AIRPORT.lng]).addTo(map).bindPopup(AIRPORT.name);
        L.circle([AIRPORT.lat, AIRPORT.lng], { radius: 40000, color: '#7FA8D6', weight: 1, fill: false, dashArray: '4 6' }).addTo(map);

        const planeLayer = L.layerGroup().addTo(map);

        function planeIcon(plane) {
            const color = PHASE_COLORS[plane.phase] || PHASE_COLORS.overflight;
            // Pesawat menghadap ke atas (utara) lalu diputar sesuai arah terbang
            return L.divIcon({
                className: '',
                iconSize: [26, 26],
                iconAnchor: [13, 13],
                html: '<svg width="26" height="26" viewBox="0 0 24 24" style="transform: rotate(' + (plane.heading || 0) + 'deg); filter: drop-shadow(0 1px 2px rgba(0,0,0,.45))">' +
                    '<path fill="' + color + '" stroke="#fff" stroke-width="1" d="M12 2c.8 0 1.4.9 1.4 2v5.2l8.1 4.8v2.2l-8.1-2.3v4.6l2.1 1.6V22L12 20.8 8.5 22v-1.9l2.1-1.6v-4.6L2.5 16.2V14l8.1-4.8V4c0-1.1.6-2 1.4-2z"/></svg>'
            });
        }

        // ---- Utilitas tampilan ----
        function el(tag, className, text) {
            const node = document.createElement(tag);
            if (className) node.className = className;
            if (text !== undefined) node.textContent = text;
            return node;
        }

        function tile(label, value, sub) {
            const box = el('div', 'bg-white/60 border border-white/80 rounded-2xl p-3');
            box.appendChild(el('p', 'text-xs text-slate-500', label));
            box.appendChild(el('p', 'text-lg font-bold text-slate-800 mt-0.5', value));
            if (sub) box.appendChild(el('p', 'text-xs text-slate-400 mt-0.5', sub));
            return box;
        }

        function formatCardinal(deg) {
            const names = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
            return names[Math.round(deg / 45) % 8];
        }

        function renderWeather(weather) {
            weatherBox.innerHTML = '';

            if (!weather) {
                weatherBox.appendChild(el('p', 'text-sm text-slate-400', t.unavailable));
                return;
            }

            const category = weather.flight_category;
            const grid = el('div', 'grid grid-cols-2 md:grid-cols-4 gap-3');

            const catBox = el('div', 'rounded-2xl p-3 text-white');
            catBox.style.backgroundColor = CAT_COLORS[category] || '#64748b';
            catBox.appendChild(el('p', 'text-xs opacity-90', 'Flight category'));
            catBox.appendChild(el('p', 'text-lg font-bold mt-0.5', category || '-'));
            catBox.appendChild(el('p', 'text-xs opacity-90 mt-0.5', t.cat[category] || ''));
            grid.appendChild(catBox);

            const windText = weather.wind_speed_kt === null || weather.wind_speed_kt === 0
                ? t.calm
                : (weather.wind_direction !== null ? formatCardinal(weather.wind_direction) + ' ' + weather.wind_direction + '\u00b0 ' : '') +
                  weather.wind_speed_kt + ' kt';
            const windSub = weather.wind_speed_kt
                ? '\u2248 ' + Math.round(weather.wind_speed_kt * 1.852) + ' km/j' + (weather.wind_gust_kt ? ' \u2022 gust ' + weather.wind_gust_kt + ' kt' : '')
                : '';
            grid.appendChild(tile(t.wind, windText, windSub));

            grid.appendChild(tile(
                t.visibility,
                weather.visibility_km === null ? '-' : (weather.visibility_is_min ? '\u2265 ' : '') + weather.visibility_km + ' km'
            ));

            grid.appendChild(tile(
                t.temperature,
                weather.temperature_c === null ? '-' : weather.temperature_c + '\u00b0C',
                weather.dewpoint_c === null ? '' : 'Dew point ' + weather.dewpoint_c + '\u00b0C'
            ));

            const cloudText = weather.clouds && weather.clouds.length
                ? weather.clouds.map(function (layer) {
                    return (COVER[layer.cover] || layer.cover) + (layer.base_ft ? ' ' + layer.base_ft.toLocaleString() + ' ft' : '');
                }).join(', ')
                : t.clear;
            const cloudBox = tile(t.clouds, cloudText, weather.ceiling_ft ? 'Ceiling ' + weather.ceiling_ft.toLocaleString() + ' ft' : '');
            cloudBox.className += ' col-span-2';
            grid.appendChild(cloudBox);

            grid.appendChild(tile(t.pressure, weather.pressure_hpa === null ? '-' : weather.pressure_hpa + ' hPa'));

            const observed = weather.observed_at
                ? new Date(weather.observed_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                : '-';
            grid.appendChild(tile(t.observed, observed));

            weatherBox.appendChild(grid);
        }

        function renderTraffic(traffic) {
            countsBox.innerHTML = '';
            listBox.innerHTML = '';
            planeLayer.clearLayers();

            if (!traffic) {
                if (noteBox) noteBox.textContent = t.trafficUnavailable;
                return;
            }

            const counts = traffic.counts;
            [['total', t.total, '#334155'], ['arriving', t.arriving, PHASE_COLORS.arriving],
             ['departing', t.departing, PHASE_COLORS.departing], ['ground', t.ground, PHASE_COLORS.ground]]
                .forEach(function (item) {
                    const box = tile(item[1], String(counts[item[0]]));
                    box.firstChild.style.color = item[2];
                    box.firstChild.style.fontWeight = '600';
                    countsBox.appendChild(box);
                });

            traffic.aircraft.forEach(function (plane, index) {
                const label = plane.callsign + ' \u2022 ' + t[plane.phase] +
                    (plane.altitude_m !== null ? ' \u2022 ' + plane.altitude_m.toLocaleString() + ' m' : '') +
                    (plane.speed_kmh !== null ? ' \u2022 ' + plane.speed_kmh + ' km/j' : '');

                L.marker([plane.lat, plane.lng], { icon: planeIcon(plane), zIndexOffset: 500 })
                    .bindTooltip(label)
                    .addTo(planeLayer);

                if (index < 8) {
                    const row = el('li', 'py-2 flex items-center justify-between gap-3');
                    const left = el('div', 'flex items-center gap-2 min-w-0');
                    const dot = el('span', 'w-2.5 h-2.5 rounded-full shrink-0');
                    dot.style.backgroundColor = PHASE_COLORS[plane.phase];
                    left.appendChild(dot);
                    left.appendChild(el('span', 'font-semibold text-slate-700 truncate', plane.callsign));
                    left.appendChild(el('span', 'text-xs text-slate-400 truncate', plane.country || ''));

                    const right = el('span', 'text-xs text-slate-500 shrink-0',
                        t[plane.phase] + (plane.altitude_m !== null ? ' \u2022 ' + plane.altitude_m.toLocaleString() + ' m' : '') +
                        ' \u2022 ' + plane.distance_km + ' km');

                    row.appendChild(left);
                    row.appendChild(right);
                    listBox.appendChild(row);
                }
            });
        }

        async function refresh() {
            try {
                const response = await fetch(LIVE_URL, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error('HTTP ' + response.status);

                const data = await response.json();

                renderWeather(data.weather);
                renderTraffic(data.traffic);
                updatedBox.textContent = t.updated + ' ' +
                    new Date(data.updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            } catch (error) {
                updatedBox.textContent = t.unavailable;
            }
        }

        refresh();
        setInterval(function () {
            if (!document.hidden) refresh();
        }, REFRESH_MS);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) refresh();
        });
    })();
    </script>
    @endif

</body>
</html>