<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroSearch - Direktori Bandara ASEAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        /* Lapisan motif batik kawung, tetap di belakang semua konten */
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body class="bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 min-h-screen flex flex-col relative text-slate-800">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Search Area -->
    <main class="w-full flex flex-col justify-center items-center px-4 py-20">
        <h1 class="text-5xl md:text-6xl font-bold text-[#7FA8D6] mb-4 text-center drop-shadow-sm hover:scale-105 transition-transform duration-500">
            Direktori Bandara ASEAN
        </h1>
        <p class="text-lg text-slate-600 mb-8 text-center max-w-2xl">
            Cari profil lengkap, alamat, website, hingga status operasional berbagai bandara di Asia Tenggara menggunakan bahasa alami.
        </p>

       <form action="{{ route('home') }}" method="GET" class="w-full max-w-3xl relative group">
            <input type="text" 
                name="search"
                value="{{ $search ?? '' }}"
                class="w-full px-6 py-4 rounded-full border-2 border-white/60 bg-white/80 backdrop-blur-sm focus:outline-none focus:border-[#7FA8D6] focus:ring-4 focus:ring-[#A8C6E7]/50 text-lg shadow-xl transition-all duration-300 group-hover:shadow-2xl"
                placeholder="Cari lokasi, status, atau kontak bandara ASEAN...">
            
            <button type="submit" class="absolute right-2 top-2 bottom-2 bg-[#FFE08A] hover:bg-[#FFF2B2] text-slate-700 font-bold px-8 rounded-full transition-all duration-300 hover:scale-105 hover:shadow-lg active:scale-95">
                Cari
            </button>
        </form>

        <!-- Sugesti / Kategori Pencarian yang Berfungsi -->
        <div class="flex flex-wrap justify-center gap-3 mt-6 text-sm items-center">
            <span class="text-slate-500 font-medium py-1">Kategori Data:</span>
            
            <a href="{{ route('category.location') }}" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">
   Lokasi & Alamat
        </a>
            
           <a href="{{ route('category.phone') }}" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">
   Website Resmi
        </a>
            
            <a href="{{ route('category.operational') }}" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">
   Status Operasional
</a>
        </div>
    </main>

<!-- Bagian Hasil Pencarian (Akan muncul pas user ngetik & klik cari) -->
    @if(isset($search) && trim($search) !== '')
    <section class="w-full max-w-6xl mx-auto px-6 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-[#7FA8D6]">Hasil Pencarian untuk: "{{ $search }}"</h2>
                <p class="text-sm text-slate-500 mt-1">Ditemukan {{ count($airports) }} bandara</p>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/80 hover:bg-white text-slate-600 rounded-xl text-sm font-semibold border border-slate-200 transition shadow-sm hover:shadow self-start sm:self-auto">
                <span>&larr;</span>
                <span>Hapus Pencarian</span>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($airports as $airport)
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border-2 border-white shadow-xl hover:shadow-2xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 bg-[#A8C6E7]/30 text-[#7FA8D6] rounded-full mb-3 inline-block">
                            {{ $airport['type'] ?? 'Airport' }}
                        </span>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $airport['name'] }}</h3>
                        <p class="text-sm text-slate-600 mb-1 flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0  1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>{{ $airport['municipality'] ?? '-' }}, {{ $airport['iso_country'] ?? '-' }}</span>
                        </p>

                        <p class="text-sm text-slate-500 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <span>ICAO / IATA: {{ $airport['ident'] ?? $airport['icao_code'] ?? '-' }} / {{ $airport['iata_code'] ?? '-' }}</span>
                        </p>
                    </div>
                    
                    <a href="{{ route('airports.show', $airport['ident'] ?? $airport['icao_code'] ?? $airport['iata_code'] ?? $airport['airport_id'] ?? $airport['id'] ?? 0) }}" 
                    class="px-4 py-3 bg-[#7FA8D6] text-white text-xs font-semibold rounded-xl hover:bg-sky-600 transition-colors shadow-sm inline-flex items-center gap-1.5 self-start">
                        <span>Lihat Detail</span>
                        <span>&rarr;</span>
                        </a>
                </div>
            @empty
                <div class="col-span-2 text-center py-12 bg-white/60 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg">
                    <p class="text-slate-500 text-lg">Tidak ada data bandara yang ditemukan.</p>
                </div>
            @endforelse
        </div>
    </section>
    @endif

    @if(!$search)

    <!-- STATISTIK -->
    <section class="w-full max-w-6xl mx-auto px-4 md:px-8 -mt-6 mb-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['value' => $stats['total'],     'label' => 'Total Bandara'],
                ['value' => $stats['countries'], 'label' => 'Negara ASEAN'],
                ['value' => $stats['large'],     'label' => 'Bandara Besar'],
                ['value' => $stats['website'],   'label' => 'Punya Website Resmi'],
            ] as $s)
                <div class="bg-white/80 backdrop-blur-md rounded-2xl border-2 border-white shadow-lg p-5 text-center hover:-translate-y-1 hover:shadow-xl transition duration-300">
                    <p class="text-3xl font-bold text-[#7FA8D6]">{{ number_format($s['value']) }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ $s['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- FITUR UTAMA -->
    <section class="w-full max-w-6xl mx-auto px-4 md:px-8 py-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-[#7FA8D6] mb-2">Apa yang Bisa Kamu Lakukan?</h2>
            <p class="text-slate-600">Semua kebutuhan informasi bandara ASEAN dalam satu tempat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            <a href="{{ route('airports.directory') }}" class="group bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-6 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-[#A8C6E7]/30 text-[#7FA8D6] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-1">Data Bandara</h3>
                <p class="text-sm text-slate-500">Jelajahi katalog lengkap berdasarkan jenis, negara, dan elevasi.</p>
            </a>

            <a href="#peta" class="group bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-6 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-[#A8C6E7]/30 text-[#7FA8D6] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-1">Bandara Terdekat</h3>
                <p class="text-sm text-slate-500">Klik titik di peta dan dapatkan 5 bandara terdekat dari lokasimu.</p>
            </a>

            <a href="{{ route('navigation.index') }}" class="group bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-6 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-[#FFE08A]/50 text-amber-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-1">Navigasi Rute</h3>
                <p class="text-sm text-slate-500">Bandingkan waktu tempuh mobil, motor, sepeda, dan jalan kaki.</p>
            </a>

            <a href="{{ route('chatbot') }}" class="group bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-6 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-[#FFE08A]/50 text-amber-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-1">Tanya AeroBot</h3>
                <p class="text-sm text-slate-500">Cari informasi bandara dengan bahasa sehari-hari lewat chatbot.</p>
            </a>

        </div>
    </section>

    <!-- BANDARA UNGGULAN -->
    @if($featured->isNotEmpty())
    <section class="w-full max-w-6xl mx-auto px-4 md:px-8 py-8">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="text-3xl font-bold text-[#7FA8D6] mb-1">Bandara Unggulan</h2>
                <p class="text-slate-600">Hub penerbangan tersibuk di Asia Tenggara.</p>
            </div>
            <a href="{{ route('airports.directory', ['type' => 'large_airport']) }}" class="text-sm font-semibold text-[#7FA8D6] hover:underline hidden sm:inline">
                Lihat semua &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($featured as $f)
                <a href="{{ route('airports.show', $f['airport_id']) }}"
                   class="bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-5 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-amber-100 text-amber-700">{{ $f['iata_code'] ?? '-' }}</span>
                        <span class="text-xs text-slate-400">{{ $f['icao_code'] ?? '-' }}</span>
                    </div>
                    <h3 class="font-bold text-slate-800 leading-snug mb-1">{{ $f['name'] }}</h3>
                    <p class="text-sm text-slate-500">{{ $f['municipality'] ?? '-' }}, {{ $f['iso_country'] ?? '-' }}</p>
                </a>
            @endforeach
        </div>
    </section>
    @endif

@endif


    <!-- Google Maps Section -->

    <!-- PETA PERSEBARAN BANDARA -->
    <section id="peta" class="w-full px-4 md:px-8 py-12">

        <div class="max-w-6xl mx-auto">

            <!-- Judul Peta -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-[#7FA8D6] mb-3">
                    Peta Persebaran Bandara
                </h2>

                <p class="text-slate-600">
                    Jelajahi lokasi bandara di seluruh Asia Tenggara.
                </p>
            </div>

            <!-- Container Peta -->
            <div class="bg-white/80 backdrop-blur-md p-3 md:p-5
                        rounded-3xl border-2 border-white shadow-xl">

                <div class="flex flex-wrap justify-between items-center gap-2 mb-4">

                    <span class="font-semibold text-slate-700">
                        Lokasi Bandara
                    </span>

                    <span id="airport-count"
                        class="text-sm text-[#7FA8D6] font-semibold">
                        Memuat data bandara...
                    </span>

                </div>

                <div id="airport-map"
                    class="w-full h-[450px] md:h-[550px] rounded-2xl z-0">
                </div>

                <p class="text-xs text-slate-500 mt-3">
                    Peta menggunakan OpenStreetMap dan Leaflet.
                    Klik peta untuk menentukan lokasi pencarian.
                </p>

            </div>


            <!-- REKOMENDASI BANDARA -->
            <div class="bg-white/90 backdrop-blur-md
                        rounded-3xl border-2 border-white
                        shadow-xl p-5 md:p-6 mt-8">

                <div class="mb-6">

                    <h2 class="text-2xl font-bold text-[#7FA8D6] mb-2">
                        5 Rekomendasi Bandara Terdekat
                    </h2>

                    <p id="recommendation-status"
                    class="text-sm text-slate-500">
                        Klik peta untuk mencari 5 bandara terdekat.
                    </p>

                </div>

                <!-- GRID REKOMENDASI -->
                <div id="recommendation-list"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">

                </div>

            </div>

        </div>

    </section>


    <!-- Floating Chatbot Button -->
    <a href="{{ route('chatbot') }}" 
   class="fixed bottom-8 right-8 group flex items-center gap-3 z-50 transition-all duration-300 hover:scale-105 active:scale-95">
    
    <!-- Teks Sapaan Kenalan -->
    <div class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-2xl shadow-lg border border-white text-slate-700 text-xs font-semibold opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all duration-300 pointer-events-none flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span>Halo aku AeroBot</span>
    </div>

    <!-- Ikon Robot SVG Tanpa Lingkaran (Langsung Wujud Robot) -->
    <div class="w-16 h-16 flex justify-center items-center drop-shadow-xl transition-transform duration-300 group-hover:-translate-y-1">
        <svg viewBox="0 0 64 64" class="w-full h-full animate-float" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Antena -->
    <path d="M32 12V7" stroke="#7FA8D6" stroke-width="3" stroke-linecap="round"/>
    <circle cx="32" cy="5" r="3.5" fill="#FDA4AF" stroke="#7FA8D6" stroke-width="2"/>

    <!-- Telinga -->
    <circle cx="8" cy="30" r="5" fill="#FFE08A" stroke="#7FA8D6" stroke-width="2"/>
    <circle cx="56" cy="30" r="5" fill="#FFE08A" stroke="#7FA8D6" stroke-width="2"/>

    <!-- Badan mungil -->
    <rect x="22" y="48" width="20" height="11" rx="5.5" fill="#FFFFFF" stroke="#7FA8D6" stroke-width="2.5"/>
    <path d="M32 56.5c-2.2-1.6-3.2-2.7-3.2-4a1.8 1.8 0 0 1 3.2-1.1 1.8 1.8 0 0 1 3.2 1.1c0 1.3-1 2.4-3.2 4z" fill="#FDA4AF"/>

    <!-- Tangan -->
    <circle cx="18" cy="53" r="3.5" fill="#FFE08A" stroke="#7FA8D6" stroke-width="2"/>
    <circle cx="46" cy="53" r="3.5" fill="#FFE08A" stroke="#7FA8D6" stroke-width="2"/>

    <!-- Kepala bulat -->
    <rect x="9" y="12" width="46" height="37" rx="18.5" fill="#FFFFFF" stroke="#7FA8D6" stroke-width="3"/>

    <!-- Layar wajah -->
    <rect x="14" y="17" width="36" height="27" rx="13" fill="#1E293B"/>

    <!-- Mata besar (berkedip) -->
    <g class="animate-blink">
        <ellipse cx="24.5" cy="29" rx="5.5" ry="6" fill="#7DD3FC"/>
        <circle cx="22.8" cy="26.8" r="2.2" fill="#FFFFFF"/>
        <circle cx="26.5" cy="31.5" r="1.1" fill="#FFFFFF"/>

        <ellipse cx="39.5" cy="29" rx="5.5" ry="6" fill="#7DD3FC"/>
        <circle cx="37.8" cy="26.8" r="2.2" fill="#FFFFFF"/>
        <circle cx="41.5" cy="31.5" r="1.1" fill="#FFFFFF"/>
    </g>

    <!-- Pipi merah muda -->
    <ellipse cx="18.5" cy="37" rx="3.2" ry="2" fill="#FDA4AF" opacity="0.85"/>
    <ellipse cx="45.5" cy="37" rx="3.2" ry="2" fill="#FDA4AF" opacity="0.85"/>

    <!-- Mulut kecil -->
    <path d="M28.5 36.5c1.2 2.2 2.5 2.2 3.5 0 1 2.2 2.3 2.2 3.5 0" stroke="#7DD3FC" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
    </div>
</a>

<style>
    /* Kedip mata */
    @keyframes blink {
        0%, 85%, 100% { transform: scaleY(1); transform-origin: center; }
        90% { transform: scaleY(0.1); transform-origin: center; }
    }
    .animate-blink {
        animation: blink 3.5s infinite;
        transform-box: fill-box;
        transform-origin: center;
    }

    /* Melayang naik-turun */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .animate-float {
        animation: float 2.8s ease-in-out infinite;
    }
</style>
    </button>

    <!-- Footer -->
    @include('layouts.footer')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // 1. DATA DAN ELEMEN HTML
    // ==========================================

    const airports = @js($mapAirports ?? []);

    const navigationPageUrl = @json(route('navigation.index'));

    const countElement =
        document.getElementById('airport-count');

    const statusElement =
        document.getElementById('recommendation-status');

    const recommendationList =
        document.getElementById('recommendation-list');

    // Periksa elemen peta
    const mapElement = document.getElementById('airport-map');

    if (!mapElement) {
        console.error('Elemen airport-map tidak ditemukan.');
        return;
    }

    if (typeof L === 'undefined') {
        if (countElement) {
            countElement.textContent = 'Leaflet gagal dimuat.';
        }
        return;
    }


    // ==========================================
    // 2. BATAS PETA ASIA TENGGARA
    // ==========================================

    const southeastAsiaBounds = L.latLngBounds(
        [-13, 90],
        [28, 142]
    );


    // ==========================================
    // 3. INISIALISASI PETA
    // ==========================================

    const map = L.map('airport-map', {
        worldCopyJump: false,
        maxBounds: southeastAsiaBounds,
        maxBoundsViscosity: 1.0,
        zoomSnap: 1,
        zoomDelta: 1,
        inertia: false
    });

    map.fitBounds(southeastAsiaBounds, {
        padding: [20, 20],
        animate: false
    });

    const minZoomAsia = map.getZoom();

    map.setMinZoom(minZoomAsia);
    map.setMaxZoom(19);

    L.tileLayer(
        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            minZoom: minZoomAsia,
            maxZoom: 19,
            noWrap: true,
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(map);


    // ==========================================
    // 4. PENAMPUNG MARKER
    // ==========================================

    const markers = [];

    const airportMarkers = new Map();

    // Layer khusus penanda 5 rekomendasi
    const recommendationLayer = L.layerGroup().addTo(map);


    // ==========================================
    // 5. MEMBUAT MARKER SELURUH BANDARA
    // ==========================================

    airports.forEach(function (airport) {

        const latitude = Number(airport.latitude_deg);
        const longitude = Number(airport.longitude_deg);

        if (
            !Number.isFinite(latitude) ||
            !Number.isFinite(longitude) ||
            latitude < -90 || latitude > 90 ||
            longitude < -180 || longitude > 180
        ) {
            return;
        }

        const marker = L.marker([
            latitude,
            longitude
        ]).addTo(map);


        // Popup informasi bandara
        const popup = document.createElement('div');

        const title = document.createElement('strong');
        title.textContent =
            airport.name ?? 'Nama bandara tidak tersedia';

        const iata = document.createElement('p');
        iata.textContent =
            'IATA: ' + (airport.iata_code ?? '-');

        const icao = document.createElement('p');
        icao.textContent =
            'ICAO: ' + (airport.icao_code ?? '-');

        const location = document.createElement('p');
        location.textContent =
            (airport.municipality ?? '-') + ', ' +
            (airport.iso_country ?? '-');

        const airportId =
            airport.ident ??
            airport.icao_code ??
            airport.iata_code ??
            airport.airport_id ??
            airport.id;

        popup.appendChild(title);
        popup.appendChild(iata);
        popup.appendChild(icao);
        popup.appendChild(location);


        // Tombol lihat profil bandara
        if (airportId) {

            const profileButton =
                document.createElement('a');

            profileButton.href =
                '/airports/' + encodeURIComponent(airportId);

            profileButton.textContent =
                'Lihat Profil Bandara';

            profileButton.className =
                'inline-block mt-3 px-4 py-2 ' +
                'bg-[#7FA8D6] hover:bg-[#6893C7] ' +
                'text-white rounded-xl text-sm font-semibold ' +
                'transition duration-300 shadow-sm hover:shadow-md';

            popup.appendChild(profileButton);
        }

        marker.bindPopup(popup);

        markers.push(marker);

        airportMarkers.set(String(airportId), marker);

    });


    // ==========================================
    // 6. JUMLAH BANDARA
    // ==========================================

    if (countElement) {

        countElement.textContent =
            markers.length + ' bandara ditemukan';

    }


    // ==========================================
    // 7. MARKER LOKASI PENGGUNA
    // ==========================================

    let userMarker = null;

    function showUserLocation(latitude, longitude) {

        const userPosition = [
            latitude,
            longitude
        ];

        // Marker lokasi pengguna
        userMarker = L.circleMarker(userPosition, {
            radius: 9,
            color: '#ffffff',
            weight: 3,
            fillColor: '#2563eb',
            fillOpacity: 1
        }).addTo(map);

        userMarker.bindPopup(
            '<strong>Lokasi Anda</strong>'
        );

        // Pastikan lokasi berada di dalam batas peta
        if (southeastAsiaBounds.contains(userPosition)) {

            map.setView(
                userPosition,
                Math.max(minZoomAsia, 8)
            );

        } else {

            if (statusElement) {
                statusElement.textContent =
                    'Lokasi Anda berada di luar cakupan peta Asia Tenggara.';
            }

        }

    }


    // ==========================================
    // 8. MENAMPILKAN 5 REKOMENDASI
    // ==========================================

    function displayRecommendations(data, userLat, userLng) {

        recommendationList.innerHTML = '';

        recommendationLayer.clearLayers();

        const recommendations = data.recommendations ?? [];

        if (recommendations.length === 0) {

            statusElement.textContent =
                'Tidak ditemukan rekomendasi bandara.';

            return;
        }

        statusElement.textContent =
            'Ditemukan ' + recommendations.length +
            ' rekomendasi bandara berdasarkan lokasi Anda.';


        recommendations.forEach(function (airport, index) {

            const latitude = Number(airport.latitude_deg);
            const longitude = Number(airport.longitude_deg);

            const airportId =
                airport.ident ??
                airport.icao_code ??
                airport.iata_code ??
                airport.airport_id ??
                airport.id;

            const distance =
                Number(airport.distance_km);

            // Penanda khusus bandara rekomendasi
            if (
                Number.isFinite(latitude) &&
                Number.isFinite(longitude) &&
                latitude >= -90 && latitude <= 90 &&
                longitude >= -180 && longitude <= 180 &&
                southeastAsiaBounds.contains([latitude, longitude])
            ) {

                const recommendationMarker =
                    L.circleMarker(
                        [latitude, longitude],
                        {
                            radius: 8,
                            color: '#ffffff',
                            weight: 2,
                            fillColor: '#ef4444',
                            fillOpacity: 1
                        }
                    );

                recommendationMarker.bindPopup(
                    '<strong>' +
                    String(airport.name ?? 'Bandara') +
                    '</strong><br>Rekomendasi #' +
                    (index + 1)
                );

                recommendationMarker.addTo(
                    recommendationLayer
                );

            }


            // Membuat kartu rekomendasi
            const card = document.createElement('div');

            card.className =
            'border border-slate-200 rounded-xl p-4 ' +
            'hover:shadow-lg hover:border-[#7FA8D6] ' +
            'transition duration-300 bg-white ' +
            'h-full flex flex-col';


            const heading = document.createElement('h3');

            heading.className =
                'font-semibold text-gray-800';

            heading.textContent =
                (index + 1) + '. ' +
                (airport.name ?? 'Nama bandara tidak tersedia');


            const details = document.createElement('p');

            details.className =
                'text-sm text-gray-500 mt-1';

            details.textContent =
                'IATA: ' + (airport.iata_code ?? '-') +
                ' | ' +
                (airport.municipality ?? '-') +
                ' | ' +
                (airport.type ?? '-');


            const distanceText = document.createElement('p');

            distanceText.className =
                'text-sm font-semibold text-blue-600 mt-2';

            distanceText.textContent =
                Number.isFinite(distance)
                    ? distance.toFixed(2) +
                      ' km dari lokasi Anda'
                    : 'Jarak tidak tersedia';


            card.appendChild(heading);
            card.appendChild(details);
            card.appendChild(distanceText);


            // Tombol lihat profil
            if (airportId) {

                const profileButton =
                    document.createElement('a');

                profileButton.href =
                    '/airports/' +
                    encodeURIComponent(airportId);

                profileButton.textContent =
                    'Lihat Profil';

                profileButton.className =
                    'inline-block mt-3 mr-2 px-3 py-2 ' +
                    'bg-[#7FA8D6] hover:bg-[#6893C7] ' +
                    'text-white rounded-xl text-sm font-semibold ' +
                    'transition duration-300 shadow-sm hover:shadow-md';
                card.appendChild(profileButton);

            }


            // Tombol navigasi Google Maps
            if (
                Number.isFinite(latitude) &&
                Number.isFinite(longitude)
            ) {

                const navigationButton =
                    document.createElement('a');

                const navigationUrl = new URL(navigationPageUrl);

                navigationUrl.searchParams.set('origin_lat', userLat);
                navigationUrl.searchParams.set('origin_lng', userLng);

                navigationUrl.searchParams.set('destination_lat', latitude);
                navigationUrl.searchParams.set('destination_lng', longitude);

                navigationUrl.searchParams.set('airport_id', airportId);
                navigationUrl.searchParams.set(
                    'airport_name',
                    airport.name ?? 'Bandara tujuan'
                );

                navigationButton.href = navigationUrl.toString();

                navigationButton.target = '_blank';

                navigationButton.rel = 'noopener noreferrer';

                navigationButton.textContent =
                    'Navigasi ke Bandara';

                navigationButton.className =
                    'inline-block mt-3 px-3 py-2 ' +
                    'bg-[#FFE08A] hover:bg-[#FFD66B] ' +
                    'text-slate-700 rounded-xl text-sm font-semibold ' +
                    'transition duration-300 shadow-sm hover:shadow-md';
                    
                card.appendChild(navigationButton);

            }


            recommendationList.appendChild(card);

        });

    }


    // ==========================================
    // 9. MENGAMBIL REKOMENDASI DARI LARAVEL
    // ==========================================

    async function getRecommendations(latitude, longitude) {

        try {

            statusElement.textContent =
                'Mencari 5 bandara terdekat...';

            const response = await fetch(
                `/api/recommendations/nearest?latitude=${latitude}&longitude=${longitude}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {

                throw new Error(
                    'HTTP Error: ' + response.status
                );

            }

            const data = await response.json();

            if (!data.success) {

                throw new Error(
                    'Gagal mengambil rekomendasi bandara.'
                );

            }

            displayRecommendations(
                data,
                latitude,
                longitude
            );

        } catch (error) {

            console.error(
                'Kesalahan rekomendasi:',
                error
            );

            statusElement.textContent =
                'Gagal memuat rekomendasi bandara.';

        }

    }


    
    // ==========================================
    // MEMILIH LOKASI MELALUI PETA LEAFLET
    // ==========================================

    statusElement.textContent =
        'Klik peta untuk mencari 5 bandara terdekat.';

    map.on('click', function (event) {

        const latitude = event.latlng.lat;
        const longitude = event.latlng.lng;

        // Hapus marker sebelumnya
        if (userMarker) {
            map.removeLayer(userMarker);
        }

        // Marker lokasi pilihan
        userMarker = L.circleMarker(
            [latitude, longitude],
            {
                radius: 9,
                color: '#ffffff',
                weight: 3,
                fillColor: '#2563eb',
                fillOpacity: 1
            }
        ).addTo(map);

        userMarker.bindPopup(
            '<strong>Lokasi Pencarian Anda</strong><br>' +
            'Latitude: ' + latitude.toFixed(6) + '<br>' +
            'Longitude: ' + longitude.toFixed(6)
        ).openPopup();

        // Ambil rekomendasi bandara
        getRecommendations(latitude, longitude);

    });

});
</script>

</body>
</html>