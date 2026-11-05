<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Operasional Bandara - AeroSearch</title>
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
            <h1 class="text-4xl font-bold text-[#7FA8D6] mb-3">Status Operasional Bandara</h1>
            <p class="text-slate-600 text-base">Informasi status layanan terjadwal, jenis fasilitas, dan keaktifan operasional bandara.</p>
        </div>

        <!-- Daftar Bandara Status Operasional -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($airports as $airport)
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border-2 border-white shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-xs font-semibold px-3 py-1 
                                @if(isset($airport['scheduled_service']) && strtolower($airport['scheduled_service']) == 'yes') bg-emerald-100 text-emerald-700 
                                @else bg-amber-100 text-amber-700 @endif rounded-full uppercase tracking-wider">
                                Layanan Terjadwal: {{ $airport['scheduled_service'] ?? 'Tidak Diketahui' }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-500">
                                {{ $airport['ident'] ?? '-' }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-1">{{ $airport['name'] ?? 'Nama Bandara' }}</h3>
                        <p class="text-sm text-slate-500 mb-4">📍 {{ $airport['municipality'] ?? 'Lokasi' }} (ISO: {{ $airport['iso_country'] ?? '-' }})</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 block">Tipe Bandara:</span>
                            <span class="text-sm font-semibold text-slate-700 capitalize">
                                {{ str_replace('_', ' ', $airport['type'] ?? 'Standard') }}
                            </span>
                        </div>
                        <a href="{{ route('airports.show', $airport['ident'] ?? 0) }}" class="px-4 py-2 bg-[#7FA8D6] text-white text-xs font-semibold rounded-xl hover:bg-sky-600 transition-colors shadow-sm">
                            Detail Lengkap ↗
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white/50 rounded-3xl border border-white">
                    <p class="text-slate-500">Tidak ada data status operasional yang ditemukan.</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- Footer -->
    @include('layouts.footer')

</body>
</html>