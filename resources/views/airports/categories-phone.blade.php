<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.cat_phone_title') }}</title>
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
<body class="bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 min-h-screen flex flex-col relative text-slate-800">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="w-full max-w-6xl mx-auto px-6 py-16 flex-grow">
        <!-- Tombol Kembali -->
        <div class="mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-semibold text-[#7FA8D6] hover:underline">
                &larr; {{ __('messages.navbar_home') }}
            </a>
        </div>

        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-[#7FA8D6] mb-3">{{ __('messages.cat_phone_heading') }}</h1>
            <p class="text-slate-600 text-base">{{ __('messages.cat_phone_sub') }}</p>
        </div>

        <!-- Daftar Bandara Kontak -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($airports as $airport)
                <div class="bg-white/85 backdrop-blur-md p-6 rounded-3xl border-2 border-white shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-xs font-semibold px-3 py-1 bg-sky-100 text-sky-700 rounded-full uppercase tracking-wider">
                                {{ $airport['type'] ?? 'Airport' }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-500">
                                ICAO: {{ $airport['ident'] ?? '-' }} | IATA: {{ $airport['iata_code'] ?? '-' }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-1">{{ $airport['name'] ?? 'Airport Name' }}</h3>
                        
                        <!-- Lokasi menggunakan SVG Icon yang natural (Tanpa Emoji) -->
                        <p class="text-sm text-slate-500 mb-4 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>{{ $airport['municipality'] ?? 'Location not available' }}, {{ $airport['iso_country'] ?? '-' }}</span>
                        </p>
                    </div>

                    <!-- Bagian Tombol Aksi (Situs Resmi & Wikipedia) -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                        <div class="flex gap-2">
                            @if(!empty($airport['home_link']))
                                <a href="{{ $airport['home_link'] }}" target="_blank" class="px-3 py-1.5 bg-[#7FA8D6] text-white text-xs font-semibold rounded-xl hover:bg-sky-600 transition-colors shadow-sm">
                                    {{ __('messages.cat_btn_official_web') }}
                                </a>
                            @endif
                            
                            @if(!empty($airport['wikipedia_link']))
                                <a href="{{ $airport['wikipedia_link'] }}" target="_blank" class="px-3 py-1.5 bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-300 transition-colors shadow-sm">
                                    {{ __('messages.cat_btn_wikipedia') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white/50 rounded-3xl border border-white">
                    <p class="text-slate-500">{{ __('messages.cat_phone_empty') }}</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($airports->lastPage() > 1)
            @php
                $current = $airports->currentPage();
                $last = $airports->lastPage();

                $start = floor(($current - 1) / 10) * 10 + 1;
                $end = min($start + 9, $last);
            @endphp

            <div class="mt-10 flex justify-center items-center gap-2 flex-wrap">
                @if ($current > 1)
                    <a href="{{ $airports->url(1) }}" class="px-3 py-2 rounded-xl bg-white border border-[#7FA8D6] text-[#7FA8D6] text-sm font-semibold hover:bg-[#7FA8D6] hover:text-white transition">{{ __('messages.pagination_first') }}</a>
                    <a href="{{ $airports->previousPageUrl() }}" class="px-3 py-2 rounded-xl bg-white border border-[#7FA8D6] text-[#7FA8D6] text-sm font-semibold hover:bg-[#7FA8D6] hover:text-white transition">&laquo; {{ __('messages.pagination_prev') }}</a>
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $current)
                        <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#7FA8D6] text-white font-bold text-sm shadow-md">{{ $page }}</span>
                    @else
                        <a href="{{ $airports->url($page) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-[#7FA8D6] hover:text-white transition">{{ $page }}</a>
                    @endif
                @endfor

                @if ($current < $last)
                    <a href="{{ $airports->nextPageUrl() }}" class="px-3 py-2 rounded-xl bg-white border border-[#7FA8D6] text-[#7FA8D6] text-sm font-semibold hover:bg-[#7FA8D6] hover:text-white transition">{{ __('messages.pagination_next') }} &raquo;</a>
                    <a href="{{ $airports->url($last) }}" class="px-3 py-2 rounded-xl bg-white border border-[#7FA8D6] text-[#7FA8D6] text-sm font-semibold hover:bg-[#7FA8D6] hover:text-white transition">{{ __('messages.pagination_last') }}</a>
                @endif
            </div>
        @endif
    </main>

    <!-- Footer -->
    @include('layouts.footer')

</body>
</html>