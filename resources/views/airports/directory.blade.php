<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Bandara - AeroSearch</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">
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
<body class="bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 min-h-screen flex flex-col text-slate-800">

@include('layouts.navbar')

@php
    $types = [
        'large_airport'  => ['label' => 'Bandara Besar',    'desc' => 'Hub utama & internasional',      'badge' => 'bg-amber-100 text-amber-700'],
        'medium_airport' => ['label' => 'Bandara Menengah', 'desc' => 'Bandara regional',               'badge' => 'bg-sky-100 text-sky-700'],
        'small_airport'  => ['label' => 'Bandara Kecil',    'desc' => 'Lokal & lapangan terbang perintis', 'badge' => 'bg-slate-100 text-slate-600'],
    ];
    $total = $typeCounts->sum();
    // Parameter yang dipertahankan saat berpindah filter jenis
    $keep = array_filter(['country' => $country, 'q' => $q, 'sort' => $sort !== 'name' ? $sort : null]);
@endphp

<main class="w-full max-w-6xl mx-auto px-4 py-12 flex-grow">

    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-[#7FA8D6]">Data Bandara</h1>
        <p class="text-slate-600 mt-2">Jelajahi seluruh bandara ASEAN berdasarkan jenis dan ukurannya.</p>
    </div>

    <!-- Kartu jenis bandara (sekaligus filter) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
        <a href="{{ route('airports.directory', $keep) }}"
           class="rounded-2xl border-2 p-4 transition hover:shadow-lg {{ !$type ? 'bg-[#7FA8D6] text-white border-[#7FA8D6]' : 'bg-white/80 border-white' }}">
            <p class="text-2xl font-bold">{{ number_format($total) }}</p>
            <p class="text-sm font-semibold">Semua Jenis</p>
            <p class="text-xs mt-1 {{ !$type ? 'text-white/80' : 'text-slate-500' }}">Seluruh bandara</p>
        </a>

        @foreach($types as $key => $meta)
            @php $count = $typeCounts[$key] ?? 0; @endphp
            <a href="{{ route('airports.directory', $keep + ['type' => $key]) }}"
               class="rounded-2xl border-2 p-4 transition hover:shadow-lg {{ $type === $key ? 'bg-[#7FA8D6] text-white border-[#7FA8D6]' : 'bg-white/80 border-white' }}">
                <p class="text-2xl font-bold">
                    {{ number_format($count) }}
                    <span class="text-xs font-medium {{ $type === $key ? 'text-white/80' : 'text-slate-400' }}">
                        ({{ round($count / max($total, 1) * 100) }}%)
                    </span>
                </p>
                <p class="text-sm font-semibold">{{ $meta['label'] }}</p>
                <p class="text-xs mt-1 {{ $type === $key ? 'text-white/80' : 'text-slate-500' }}">{{ $meta['desc'] }}</p>
            </a>
        @endforeach
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('airports.directory') }}"
          class="bg-white/80 backdrop-blur-md rounded-2xl border-2 border-white shadow-md p-4 mb-6 flex flex-col lg:flex-row gap-3">
        @if($type) <input type="hidden" name="type" value="{{ $type }}"> @endif

        <div class="relative flex-1">
            <svg class="w-5 h-5 text-[#7FA8D6] absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, kota, kode ICAO/IATA, atau kata kunci..."
                   class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-[#A8C6E7] bg-white focus:outline-none focus:ring-2 focus:ring-[#7FA8D6]">
        </div>

        <select name="country" class="lg:w-52 px-3 py-2.5 rounded-xl border border-[#A8C6E7] bg-white focus:outline-none focus:ring-2 focus:ring-[#7FA8D6]">
            <option value="">Semua Negara</option>
            @foreach($countries as $c)
                <option value="{{ $c }}" @selected($country === $c)>{{ $c }}</option>
            @endforeach
        </select>

        <select name="sort" class="lg:w-48 px-3 py-2.5 rounded-xl border border-[#A8C6E7] bg-white focus:outline-none focus:ring-2 focus:ring-[#7FA8D6]">
            <option value="name" @selected($sort === 'name')>Urut: Nama (A-Z)</option>
            <option value="elevation" @selected($sort === 'elevation')>Urut: Elevasi tertinggi</option>
        </select>

        <button type="submit" class="px-6 py-2.5 bg-[#7FA8D6] hover:bg-[#6893C7] text-white font-semibold rounded-xl transition shadow-sm">
            Terapkan
        </button>

        @if($type || $country || $q || $sort !== 'name')
            <a href="{{ route('airports.directory') }}"
               class="px-5 py-2.5 bg-[#FFE08A] hover:bg-[#FFD66B] text-slate-700 font-semibold rounded-xl transition text-center">
                Reset
            </a>
        @endif
    </form>

    <p class="text-sm text-slate-500 mb-4">
        Menampilkan {{ number_format($airports->total()) }} bandara
        @if($type) · jenis: <b>{{ $types[$type]['label'] ?? $type }}</b> @endif
        @if($country) · negara: <b>{{ $country }}</b> @endif
    </p>

    <!-- Daftar bandara -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($airports as $airport)
            @php
                $meta = $types[$airport['type'] ?? ''] ?? ['label' => 'Lainnya', 'badge' => 'bg-slate-100 text-slate-600'];
                $elev = isset($airport['elevation_ft']) ? round($airport['elevation_ft'] * 0.3048) : null;
            @endphp

            <div class="bg-white/80 backdrop-blur-md p-5 rounded-3xl border-2 border-white shadow-lg hover:shadow-2xl transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full {{ $meta['badge'] }}">
                            {{ $meta['label'] }}
                        </span>

                        <div class="flex items-center gap-2 text-slate-400">
                            @if(!empty($airport['home_link']))
                                <a href="{{ $airport['home_link'] }}" target="_blank" rel="noopener noreferrer" title="Website resmi" class="hover:text-[#7FA8D6] transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                </a>
                            @endif
                            @if(!empty($airport['wikipedia_link']))
                                <a href="{{ $airport['wikipedia_link'] }}" target="_blank" rel="noopener noreferrer" title="Wikipedia" class="hover:text-[#7FA8D6] transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-800 mb-2 leading-snug">{{ $airport['name'] ?? '-' }}</h3>

                    <p class="text-sm text-slate-600 flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $airport['municipality'] ?? '-' }}, {{ $airport['iso_country'] ?? '-' }}</span>
                    </p>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-[#A8C6E7]/25 text-slate-600">
                            ICAO <b>{{ $airport['icao_code'] ?? '-' }}</b>
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-[#A8C6E7]/25 text-slate-600">
                            IATA <b>{{ $airport['iata_code'] ?? '-' }}</b>
                        </span>
                        @if($elev !== null)
                            <span class="px-2.5 py-1 rounded-lg bg-[#FFE08A]/40 text-slate-600">
                                <b>{{ number_format($elev) }}</b> mdpl
                            </span>
                        @endif
                    </div>
                </div>

                <a href="{{ route('airports.show', $airport['airport_id']) }}"
                   class="mt-5 self-start inline-flex items-center gap-1.5 px-4 py-2 bg-[#7FA8D6] hover:bg-[#6893C7] text-white text-xs font-semibold rounded-xl transition shadow-sm">
                    Lihat Detail
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white/60 rounded-3xl border-2 border-white">
                <p class="text-slate-500 text-lg">Tidak ada bandara yang cocok dengan filter.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $airports->links() }}</div>
</main>

@include('layouts.footer')
</body>
</html>