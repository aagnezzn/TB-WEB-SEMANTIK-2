<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $airport['name'] ?? 'Detail Bandara' }} - AeroSearch</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">
    
    <!-- Open Graph Protocol (Meta Tags Media Sosial / Web Semantik) -->
    <meta property="og:title" content="{{ $airport['name'] ?? 'Detail Bandara' }} - AeroSearch" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="Informasi lengkap bandara {{ $airport['name'] ?? '' }} di {{ $airport['municipality'] ?? '-' }}, {{ $airport['iso_country'] ?? '-' }}. Kode ICAO: {{ $airport['ident'] ?? '-' }}." />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:site_name" content="AeroSearch ASEAN" />

    <script src="https://cdn.tailwindcss.com"></script>
    
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
                        {{ $airport['name'] ?? 'Nama Bandara Tidak Tersedia' }}
                    </h1>
                </div>

                <!-- Status Layanan Berjadwal -->
                @if(isset($airport['scheduled_service']))
                <div class="bg-[#A8C6E7]/20 px-4 py-2.5 rounded-2xl border border-[#A8C6E7]/40 shrink-0">
                    <p class="text-xs font-medium text-slate-500 mb-0.5">Layanan Berjadwal</p>
                    <p class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Jam (Ganti Emoji 🕒) -->
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @if($airport['scheduled_service'] == 'yes' || $airport['scheduled_service'] === true || $airport['scheduled_service'] == '1')
                            <span class="text-emerald-600 font-bold">Ya (Komersial Rutin)</span>
                        @else
                            <span class="text-slate-500">Tidak (Non-Komersial / Privat)</span>
                        @endif
                    </p>
                </div>
                @endif
            </div>

            <!-- Grid Informasi Detail Lainnya -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200/60 pt-6">
                
                <!-- Lokasi / Kota (RDFa Place & PostalAddress) -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80" property="location" typeof="Place">
                    <p class="text-xs font-medium text-slate-500 mb-1">Lokasi / Kota</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2" property="address" typeof="PostalAddress">
                        <!-- SVG Pin Lokasi (Ganti Emoji 📍) -->
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span><span property="addressLocality">{{ $airport['municipality'] ?? '-' }}</span>, <span property="addressCountry">{{ $airport['iso_country'] ?? '-' }}</span></span>
                    </p>
                </div>

                <!-- Kode ICAO / IATA -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">Kode ICAO / IATA</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Pesawat (Ganti Emoji ✈️) -->
                        <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span><span property="icaoCode">{{ $airport['ident'] ?? '-' }}</span> / <span property="iataCode">{{ $airport['iata_code'] ?? '-' }}</span></span>
                    </p>
                </div>

                <!-- Koordinat (RDFa GeoCoordinates) -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80" property="geo" typeof="GeoCoordinates">
                    <p class="text-xs font-medium text-slate-500 mb-1">Koordinat (Lat, Long)</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Globe / Koordinat (Ganti Emoji 🌍) -->
                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><span property="latitude">{{ $airport['latitude_deg'] ?? '-' }}</span>, <span property="longitude">{{ $airport['longitude_deg'] ?? '-' }}</span></span>
                    </p>
                </div>

                <!-- Benua / Wilayah -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">Benua / Wilayah</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Globe Alt (Ganti Emoji 🌐) -->
                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <span>{{ $airport['continent'] ?? '-' }}</span>
                    </p>
                </div>

                <!-- Situs Web Resmi (RDFa url) -->
                @if(!empty($airport['home_link']) || !empty($airport['website']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">Situs Web Resmi</p>
                    <a property="url" href="{{ $airport['home_link'] ?? $airport['website'] }}" target="_blank" rel="noopener noreferrer" class="text-base font-semibold text-[#7FA8D6] hover:underline break-all flex items-center gap-2">
                        <!-- SVG Link (Ganti Emoji 🔗) -->
                        <svg class="w-4 h-4 text-[#7FA8D6] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        <span>Kunjungi Website Bandara &rarr;</span>
                    </a>
                </div>
                @endif

                <!-- Referensi Wikipedia -->
                @if(!empty($airport['wikipedia_link']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">Referensi Wikipedia</p>
                    <a href="{{ $airport['wikipedia_link'] }}" target="_blank" rel="noopener noreferrer" class="text-base font-semibold text-[#7FA8D6] hover:underline break-all flex items-center gap-2">
                        <!-- SVG Buku / Wikipedia (Ganti Emoji 📖) -->
                        <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Buka Halaman Wikipedia &rarr;</span>
                    </a>
                </div>
                @endif

                <!-- Kontak / Telepon (RDFa telephone) -->
                @if(!empty($airport['phone']) || !empty($airport['contact']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80 md:col-span-2">
                    <p class="text-xs font-medium text-slate-500 mb-1">Kontak / Telepon</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <!-- SVG Telepon (Ganti Emoji 📞) -->
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span property="telephone">{{ $airport['phone'] ?? $airport['contact'] }}</span>
                    </p>
                </div>
                @endif

            </div>

            <!-- Tombol Kembali di Kanan Bawah -->
            <div class="mt-8 pt-6 border-t border-slate-200/60 flex justify-end">
                <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm font-semibold bg-[#7FA8D6] hover:bg-[#6893c7] text-white px-5 py-2.5 rounded-xl transition shadow-sm gap-2">
                    <span>&larr; Kembali ke Hasil Pencarian</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    @include('layouts.footer')

</body>
</html>