<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nomor Telepon & Kontak Bandara - AeroSearch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 min-h-screen flex flex-col relative text-slate-800">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="w-full max-w-6xl mx-auto px-6 py-16 flex-grow">
        <!-- Tombol Kembali -->
        <div class="mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-semibold text-[#7FA8D6] hover:underline">
                &larr; Kembali ke Beranda
            </a>
        </div>

        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-[#7FA8D6] mb-3">Direktori Kontak & Website Bandara</h1>
            <p class="text-slate-600 text-base">Informasi situs resmi, referensi, dan saluran komunikasi bandara di kawasan regional.</p>
        </div>

        <!-- Daftar Bandara Kontak -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($airports as $airport)
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border-2 border-white shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-xs font-semibold px-3 py-1 bg-sky-100 text-sky-700 rounded-full uppercase tracking-wider">
                                {{ $airport['type'] ?? 'Airport' }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-500">
                                ICAO: {{ $airport['ident'] ?? '-' }} | IATA: {{ $airport['iata_code'] ?? '-' }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-1">{{ $airport['name'] ?? 'Nama Bandara' }}</h3>
                        <p class="text-sm text-slate-500 mb-4">📍 {{ $airport['municipality'] ?? 'Lokasi tidak tersedia' }}, Negara: ({{ $airport['iso_country'] ?? '-' }})</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex flex-wrap gap-3 items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 block">Nomor Telepon:</span>
                            <span class="text-sm font-medium text-slate-700">
                                {{ $airport['phone'] ?? 'Tidak tersedia (Cek situs resmi)' }}
                            </span>
                        </div>
                        <div class="flex gap-2">
                            @if(!empty($airport['home_link']))
                                <a href="{{ $airport['home_link'] }}" target="_blank" class="px-3 py-1.5 bg-[#7FA8D6] text-white text-xs font-semibold rounded-xl hover:bg-sky-600 transition-colors">
                                    Situs Resmi ↗
                                </a>
                            @endif
                            @if(!empty($airport['wikipedia_link']))
                                <a href="{{ $airport['wikipedia_link'] }}" target="_blank" class="px-3 py-1.5 bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-300 transition-colors">
                                    Wikipedia ↗
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white/50 rounded-3xl border border-white">
                    <p class="text-slate-500">Tidak ada data kontak bandara yang ditemukan.</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- Footer -->
    @include('layouts.footer')

</body>
</html>