<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori Bandara ASEAN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
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
            Cari profil lengkap, alamat, kontak, hingga status operasional berbagai bandara di Asia Tenggara menggunakan bahasa alami.
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
   Nomor Telepon & Kontak
        </a>
            
            <a href="{{ route('category.operational') }}" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">
   Status Operasional
</a>
        </div>
    </main>

<!-- Bagian Hasil Pencarian (Akan muncul pas user ngetik & klik cari) -->
    @if(isset($search))
    <section class="w-full max-w-6xl mx-auto px-6 py-10">
        <h2 class="text-2xl font-bold text-[#7FA8D6] mb-6">Hasil Pencarian untuk: "{{ $search }}"</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($airports as $airport)
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border-2 border-white shadow-xl hover:shadow-2xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 bg-[#A8C6E7]/30 text-[#7FA8D6] rounded-full mb-3 inline-block">
                            {{ $airport['type'] ?? 'Airport' }}
                        </span>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $airport['name'] }}</h3>
                        <p class="text-sm text-slate-600 mb-1">📍 {{ $airport['municipality'] ?? '-' }}, {{ $airport['iso_country'] ?? '-' }}</p>
                        <p class="text-sm text-slate-500 mb-4">✈️ ICAO / IATA: {{ $airport['ident'] }} / {{ $airport['iata_code'] ?? '-' }}</p>
                    </div>
                    
                    <a href="{{ route('airports.show', $airport['airport_id']) }}" 
                       class="inline-block text-center text-sm bg-[#7FA8D6] hover:bg-[#6893c7] text-white font-semibold px-4 py-2.5 rounded-xl transition shadow-sm">
                        Lihat Detail Bandara &rarr;
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


    <!-- Google Maps Section -->
    
    <!-- Peta Persebaran Bandara -->
    <section id="peta" class="w-full px-4 md:px-8 py-12">
        <div class="max-w-6xl mx-auto">

            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-[#7FA8D6] mb-3">
                    Peta Persebaran Bandara
                </h2>

                <p class="text-slate-600">
                    Jelajahi lokasi bandara di seluruh Asia Tenggara.
                </p>
            </div>

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
                </p>
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
        <svg viewBox="0 0 64 64" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Antena Atas -->
            <path d="M32 12V6" stroke="#7FA8D6" stroke-width="4" stroke-linecap="round"/>
            <circle cx="32" cy="4" r="3" fill="#FFE08A" stroke="#7FA8D6" stroke-width="2"/>
            
            <!-- Telinga Kiri & Kanan -->
            <rect x="8" y="26" width="6" height="12" rx="3" fill="#CBD5E1" stroke="#7FA8D6" stroke-width="2"/>
            <rect x="50" y="26" width="6" height="12" rx="3" fill="#CBD5E1" stroke="#7FA8D6" stroke-width="2"/>
            
            <!-- Kepala Utama Robot -->
            <rect x="12" y="14" width="40" height="30" rx="12" fill="#F8FAFC" stroke="#7FA8D6" stroke-width="3"/>
            
            <!-- Layar Muka (Warna Gelap) -->
            <rect x="17" y="19" width="30" height="20" rx="8" fill="#1E293B"/>
            
            <!-- Mata Robot (Bercahaya & Bisa Kedip) -->
            <g class="animate-blink">
                <!-- Mata Kiri -->
                <ellipse cx="26" cy="27" rx="4" ry="4" fill="#38BDF8"/>
                <circle cx="25" cy="26" r="1.5" fill="#FFFFFF"/>
                
                <!-- Mata Kanan -->
                <ellipse cx="38" cy="27" rx="4" ry="4" fill="#38BDF8"/>
                <circle cx="37" cy="26" r="1.5" fill="#FFFFFF"/>
            </g>
            
            <!-- Senyum Robot Kecil -->
            <path d="M30 33C30 33 31.5 34.5 32 34.5C32.5 34.5 34 33 34 33" stroke="#38BDF8" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </div>
</a>

<style>
    /* Animasi Kedip Mata Robot */
    @keyframes blink {
        0%, 85%, 100% { transform: scaleY(1); transform-origin: center; }
        90% { transform: scaleY(0.1); transform-origin: center; }
    }
    .animate-blink {
        animation: blink 3.5s infinite;
    }
</style>
    </button>

    <!-- Footer -->
    @include('layouts.footer')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const airports = @js($mapAirports ?? []);

    const countElement = document.getElementById('airport-count');

    // Periksa apakah Leaflet berhasil dimuat
    if (typeof L === 'undefined') {
        countElement.textContent = 'Leaflet gagal dimuat.';
        return;
    }

    // Inisialisasi peta Asia Tenggara
    const map = L.map('airport-map').setView([3, 115], 4);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markers = [];

    // Membuat marker dari dataset bandara
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

        const marker = L.marker([latitude, longitude]).addTo(map);

const popup = document.createElement('div');

const title = document.createElement('strong');
title.textContent = airport.name ?? 'Nama bandara tidak tersedia';

const iata = document.createElement('p');
iata.textContent = 'IATA: ' + (airport.iata_code ?? '-');

const icao = document.createElement('p');
icao.textContent = 'ICAO: ' + (airport.icao_code ?? '-');

const location = document.createElement('p');
location.textContent =
    (airport.municipality ?? '-') + ', ' +
    (airport.iso_country ?? '-');

// Mengambil ID bandara
const airportId =
    airport.airport_id ??
    airport.id ??
    airport.ident ??
    airport.iata_code;

// Membuat tombol lihat profil
if (airportId) {
    const profileButton = document.createElement('a');

    profileButton.href =
        '/airports/' + encodeURIComponent(airportId);

    profileButton.textContent = 'Lihat Profil Bandara';

    profileButton.className =
        'inline-block mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold';

    popup.appendChild(profileButton);
}

popup.prepend(location);
popup.prepend(icao);
popup.prepend(iata);
popup.prepend(title);

marker.bindPopup(popup);

markers.push(marker);

        markers.push(marker);
    });

    // Tampilkan jumlah marker
    countElement.textContent =
        markers.length + ' bandara ditemukan';

    // Atur zoom agar semua bandara terlihat
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    } else {
        countElement.textContent =
            'Tidak ada data koordinat bandara.';
    }

});
</script>

</body>
</html>