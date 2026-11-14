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
    </style>
</head>
<body class="bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 min-h-screen flex flex-col relative text-slate-800">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Detail Content dengan Anotasi RDFa (schema.org/Airport)[cite: 1] -->
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
                    <p class="text-sm font-semibold text-slate-700 flex items-center gap-1.5">
                        <span>🕒</span> 
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
                        <span>📍</span> 
                        <span property="addressLocality">{{ $airport['municipality'] ?? '-' }}</span>, 
                        <span property="addressCountry">{{ $airport['iso_country'] ?? '-' }}</span>
                    </p>
                </div>

                <!-- Kode ICAO / IATA -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">Kode ICAO / IATA</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <span>✈️</span> 
                        <span property="icaoCode">{{ $airport['ident'] ?? '-' }}</span> / 
                        <span property="iataCode">{{ $airport['iata_code'] ?? '-' }}</span>
                    </p>
                </div>

                <!-- Koordinat (RDFa GeoCoordinates) -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80" property="geo" typeof="GeoCoordinates">
                    <p class="text-xs font-medium text-slate-500 mb-1">Koordinat (Lat, Long)</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <span>🌍</span> 
                        <span property="latitude">{{ $airport['latitude_deg'] ?? '-' }}</span>, 
                        <span property="longitude">{{ $airport['longitude_deg'] ?? '-' }}</span>
                    </p>
                </div>

                <!-- Benua / Wilayah -->
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">Benua / Wilayah</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <span>🌐</span> {{ $airport['continent'] ?? '-' }}
                    </p>
                </div>

                <!-- Situs Web Resmi (RDFa url) -->
                @if(!empty($airport['home_link']) || !empty($airport['website']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">Situs Web Resmi</p>
                    <a property="url" href="{{ $airport['home_link'] ?? $airport['website'] }}" target="_blank" rel="noopener noreferrer" class="text-base font-semibold text-[#7FA8D6] hover:underline break-all flex items-center gap-2">
                        <span>🔗</span> Kunjungi Website Bandara &rarr;
                    </a>
                </div>
                @endif

                <!-- Referensi Wikipedia -->
                @if(!empty($airport['wikipedia_link']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80">
                    <p class="text-xs font-medium text-slate-500 mb-1">Referensi Wikipedia</p>
                    <a href="{{ $airport['wikipedia_link'] }}" target="_blank" rel="noopener noreferrer" class="text-base font-semibold text-[#7FA8D6] hover:underline break-all flex items-center gap-2">
                        <span>📖</span> Buka Halaman Wikipedia &rarr;
                    </a>
                </div>
                @endif

                <!-- Kontak / Telepon (RDFa telephone) -->
                @if(!empty($airport['phone']) || !empty($airport['contact']))
                <div class="bg-white/50 p-4 rounded-2xl border border-white/80 md:col-span-2">
                    <p class="text-xs font-medium text-slate-500 mb-1">Kontak / Telepon</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <span>📞</span> <span property="telephone">{{ $airport['phone'] ?? $airport['contact'] }}</span>
                    </p>
                </div>
                @endif

            </div>

            <!-- Tombol Kembali di Kanan Bawah -->
            <div class="mt-8 pt-6 border-t border-slate-200/60 flex justify-end">
                <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm font-semibold bg-[#7FA8D6] hover:bg-[#6893c7] text-white px-5 py-2.5 rounded-xl transition shadow-sm">
                    &larr; Kembali ke Hasil Pencarian
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    @include('layouts.footer')

</body>
</html>