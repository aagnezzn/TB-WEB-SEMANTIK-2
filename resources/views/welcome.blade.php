<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.welcome_page_title') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">
    @include('layouts.og', ['ogTitle' => __('messages.welcome_page_title'), 'ogDescription' => 'Cari dan jelajahi bandara se-ASEAN berbasis data semantik (RDF, SPARQL, DBpedia).'])
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
            {{ __('messages.welcome_hero_title') }}
        </h1>
        <p class="text-lg text-slate-600 mb-8 text-center max-w-2xl">
            {{ __('messages.welcome_hero_subtitle') }}
        </p>

       <form id="search-form" action="{{ route('home') }}" method="GET" class="w-full max-w-3xl relative group" role="search">
            <input type="text"
                id="search-input"
                name="search"
                value="{{ $search ?? '' }}"
                autocomplete="off"
                role="combobox"
                aria-expanded="false"
                aria-controls="search-suggestions"
                aria-autocomplete="list"
                class="w-full px-6 py-4 pr-36 rounded-full border-2 border-white/60 bg-white/80 backdrop-blur-sm focus:outline-none focus:border-[#7FA8D6] focus:ring-4 focus:ring-[#A8C6E7]/50 text-lg shadow-xl transition-all duration-300 group-hover:shadow-2xl"
                placeholder="{{ __('messages.welcome_search_placeholder') }}">

            <button type="submit" class="absolute right-2 top-2 bottom-2 bg-[#FFE08A] hover:bg-[#FFF2B2] text-slate-700 font-bold px-8 rounded-full transition-all duration-300 hover:scale-105 hover:shadow-lg active:scale-95">
                {{ __('messages.welcome_search_btn') }}
            </button>

            <!-- Saran pencarian huruf per huruf -->
            <div id="search-suggestions" role="listbox"
                class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-[#A8C6E7] rounded-2xl shadow-2xl max-h-96 overflow-y-auto z-[1000] text-left">
            </div>
        </form>

        <!-- Pesan Saran Typo di Bawah Search Form -->
        @if(!empty($suggestion))
            <div class="mt-4 px-5 py-3 bg-white/95 backdrop-blur-md border border-amber-300 rounded-2xl shadow-md text-sm text-slate-700 flex items-center gap-2 max-w-3xl w-full justify-center transition-all">
                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>
                    {{ __('messages.search_did_you_mean') }}
                    <a href="{{ route('home', ['search' => $suggestion['term']]) }}" class="font-bold text-[#7FA8D6] hover:text-sky-700 underline ml-1">
                        {{ $suggestion['display'] }}
                    </a>?
                </span>
            </div>
        @endif

        <!-- Sugesti / Kategori Pencarian yang Berfungsi -->
        <div class="flex flex-wrap justify-center gap-3 mt-6 text-sm items-center">
            <span class="text-slate-500 font-medium py-1">{{ __('messages.welcome_cat_all') }}:</span>
            
            <a href="{{ route('category.location') }}" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">
                {{ __('messages.welcome_cat_location') }}
            </a>
            
           <a href="{{ route('category.phone') }}" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">
                {{ __('messages.welcome_cat_contact') }}
            </a>
            
            <a href="{{ route('category.operational') }}" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">
                {{ __('messages.welcome_cat_operational') }}
            </a>
        </div>
    </main>

<!-- Bagian Hasil Pencarian (Akan muncul pas user ngetik & klik cari) -->
    @if(isset($search) && trim($search) !== '')
    <section class="w-full max-w-6xl mx-auto px-6 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-[#7FA8D6]">{!! __('messages.welcome_search_results_for', ['query' => e($search)]) !!}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ __('messages.welcome_found_airports', ['count' => count($airports)]) }}</p>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/80 hover:bg-white text-slate-600 rounded-xl text-sm font-semibold border border-slate-200 transition shadow-sm hover:shadow self-start sm:self-auto">
                <span>&larr;</span>
                <span>{{ __('messages.welcome_clear_search') }}</span>
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
                        <span>{{ __('messages.welcome_view_details') }}</span>
                        <span>&rarr;</span>
                        </a>
                </div>
            @empty
                <div class="col-span-2 text-center py-12 bg-white/60 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg px-6">
                    <p class="text-slate-600 text-lg font-medium mb-2">{{ __('messages.welcome_no_airports_found') }}</p>

                    @if(!empty($suggestion))
                        <div class="inline-flex items-center gap-2 mt-2 px-4 py-2 bg-amber-50 border border-amber-200 rounded-xl text-slate-700 text-sm">
                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ __('messages.search_did_you_mean') }}</span>
                            <a href="{{ route('home', ['search' => $suggestion['term']]) }}" class="font-bold text-[#7FA8D6] hover:text-sky-700 underline">
                                {{ $suggestion['display'] }}
                            </a>?
                        </div>
                    @else
                        <p class="text-xs text-slate-400 mt-1">{{ __('messages.welcome_try_other_keywords') }}</p>
                    @endif
                </div>
            @endforelse
        </div>

        @if(count($airports) === 0 && !empty($suggestedAirports))
        <div class="mt-10">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span>{{ __('messages.search_suggested_results') }}</span>
                    <a href="{{ route('home', ['search' => $suggestion['term']]) }}" class="text-[#7FA8D6] underline text-sm font-semibold hover:text-sky-700 ml-1">
                        "{{ $suggestion['display'] }}"
                    </a>
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($suggestedAirports as $airport)
                    <div class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border-2 border-amber-200/60 shadow-xl hover:shadow-2xl transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 bg-amber-100 text-amber-700 rounded-full mb-3 inline-block">
                                {{ $airport['type'] ?? 'Airport' }}
                            </span>
                            <h4 class="text-xl font-bold text-slate-800 mb-2">{{ $airport['name'] }}</h4>
                            <p class="text-sm text-slate-600 mb-1 flex items-center gap-2">
                                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
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
                            <span>{{ __('messages.welcome_view_details') }}</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        </div>
    </section>
    @endif

    @if(!$search)

    <!-- STATISTIK -->
    <section class="w-full max-w-6xl mx-auto px-4 md:px-8 -mt-6 mb-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['value' => $stats['total'],     'label' => __('messages.welcome_stat_total')],
                ['value' => $stats['countries'], 'label' => __('messages.welcome_stat_countries')],
                ['value' => $stats['large'],     'label' => __('messages.welcome_stat_commercial')],
                ['value' => $stats['website'],   'label' => __('messages.welcome_stat_coverage')],
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
            <h2 class="text-3xl font-bold text-[#7FA8D6] mb-2">{{ __('messages.welcome_what_can_you_do') }}</h2>
            <p class="text-slate-600">{{ __('messages.welcome_what_can_you_do_sub') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            <a href="{{ route('airports.directory') }}" class="group bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-6 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-[#A8C6E7]/30 text-[#7FA8D6] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-1">{{ __('messages.welcome_card_dir_title') }}</h3>
                <p class="text-sm text-slate-500">{{ __('messages.welcome_card_dir_desc') }}</p>
            </a>

            <a href="#peta" class="group bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-6 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-[#A8C6E7]/30 text-[#7FA8D6] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-1">{{ __('messages.welcome_card_nearby_title') }}</h3>
                <p class="text-sm text-slate-500">{{ __('messages.welcome_card_nearby_desc') }}</p>
            </a>

            <a href="{{ route('navigation.index') }}" class="group bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-6 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-[#FFE08A]/50 text-amber-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-1">{{ __('messages.welcome_card_nav_title') }}</h3>
                <p class="text-sm text-slate-500">{{ __('messages.welcome_card_nav_desc') }}</p>
            </a>

            <a href="{{ route('chatbot') }}" class="group bg-white/80 backdrop-blur-md rounded-3xl border-2 border-white shadow-lg p-6 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-[#FFE08A]/50 text-amber-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-1">{{ __('messages.welcome_card_bot_title') }}</h3>
                <p class="text-sm text-slate-500">{{ __('messages.welcome_card_bot_desc') }}</p>
            </a>

        </div>
    </section>

    <!-- BANDARA UNGGULAN -->
    @if($featured->isNotEmpty())
    <section class="w-full max-w-6xl mx-auto px-4 md:px-8 py-8">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="text-3xl font-bold text-[#7FA8D6] mb-1">{{ __('messages.welcome_featured_title') }}</h2>
                <p class="text-slate-600">{{ __('messages.welcome_featured_sub') }}</p>
            </div>
            <a href="{{ route('airports.directory', ['type' => 'large_airport']) }}" class="text-sm font-semibold text-[#7FA8D6] hover:underline hidden sm:inline">
                {{ __('messages.welcome_view_all') }} &rarr;
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
                    {{ __('messages.welcome_map_title') }}
                </h2>

                <p class="text-slate-600">
                    {{ __('messages.welcome_map_sub') }}
                </p>
            </div>

            <!-- Container Peta -->
            <div class="bg-white/80 backdrop-blur-md p-3 md:p-5
                        rounded-3xl border-2 border-white shadow-xl">

                <div class="flex flex-wrap justify-between items-center gap-2 mb-4">

                    <span class="font-semibold text-slate-700">
                        {{ __('messages.welcome_map_locations_title') }}
                    </span>

                    <span id="airport-count"
                        class="text-sm text-[#7FA8D6] font-semibold">
                        {{ __('messages.welcome_map_loading') }}
                    </span>

                </div>

                <div id="airport-map"
                    class="w-full h-[450px] md:h-[550px] rounded-2xl z-0">
                </div>

                <p class="text-xs text-slate-500 mt-3">
                    {{ __('messages.welcome_map_footnote') }}
                </p>

            </div>


            <!-- REKOMENDASI BANDARA -->
            <div class="bg-white/90 backdrop-blur-md
                        rounded-3xl border-2 border-white
                        shadow-xl p-5 md:p-6 mt-8">

                <div class="mb-6">

                    <h2 class="text-2xl font-bold text-[#7FA8D6] mb-2">
                        {{ __('messages.welcome_rec_title') }}
                    </h2>

                    <p id="recommendation-status"
                    class="text-sm text-slate-500">
                        {{ __('messages.welcome_rec_click_hint') }}
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
        <span>{{ __('messages.welcome_bot_greeting_badge') }}</span>
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

    const i18n = {
        failedLeaflet: @json(__('messages.welcome_map_leaflet_fail')),
        airportsFound: @json(__('messages.welcome_map_airports_found')),
        yourLocation: @json(__('messages.welcome_js_your_location')),
        outsideAsean: @json(__('messages.welcome_rec_outside')),
        recEmpty: @json(__('messages.welcome_rec_empty')),
        recFound: @json(__('messages.welcome_rec_found')),
        recBadge: @json(__('messages.welcome_rec_badge')),
        recDistance: @json(__('messages.welcome_rec_distance')),
        recDistNa: @json(__('messages.welcome_rec_dist_na')),
        viewProfile: @json(__('messages.welcome_rec_view_profile')),
        navigateAirport: @json(__('messages.welcome_rec_navigate')),
        searchingNearest: @json(__('messages.welcome_rec_searching')),
        recError: @json(__('messages.welcome_rec_err_load')),
        searchLocation: @json(__('messages.welcome_rec_search_loc')),
        viewAirportProfile: @json(__('messages.welcome_rec_view_profile')),
        airportNameNa: @json(__('messages.airport_not_found')),
        clickMapHint: @json(__('messages.welcome_rec_click_hint'))
    };

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
            countElement.textContent = i18n.failedLeaflet;
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
            airport.name ?? 'Airport name not available';

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
                i18n.viewAirportProfile;

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
            i18n.airportsFound.replace(':count', markers.length);

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
            '<strong>' + i18n.yourLocation + '</strong>'
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
                    i18n.outsideAsean;
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
                i18n.recEmpty;

            return;
        }

        statusElement.textContent =
            i18n.recFound.replace(':count', recommendations.length);


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
                    String(airport.name ?? 'Airport') +
                    '</strong><br>' + i18n.recBadge +
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
                (airport.name ?? 'Airport name not available');


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
                    ? i18n.recDistance.replace(':dist', distance.toFixed(2))
                    : i18n.recDistNa;


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
                    i18n.viewProfile;

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
                    airport.name ?? 'Destination airport'
                );

                navigationButton.href = navigationUrl.toString();

                navigationButton.target = '_blank';

                navigationButton.rel = 'noopener noreferrer';

                navigationButton.textContent =
                    i18n.navigateAirport;

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
                i18n.searchingNearest;

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
                    'Failed to retrieve airport recommendations.'
                );

            }

            displayRecommendations(
                data,
                latitude,
                longitude
            );

        } catch (error) {

            console.error(
                'Recommendation error:',
                error
            );

            statusElement.textContent =
                i18n.recError;

        }

    }


    
    // ==========================================
    // MEMILIH LOKASI MELALUI PETA LEAFLET
    // ==========================================

    statusElement.textContent =
        i18n.clickMapHint;

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
            '<strong>' + i18n.searchLocation + '</strong><br>' +
            'Latitude: ' + latitude.toFixed(6) + '<br>' +
            'Longitude: ' + longitude.toFixed(6)
        ).openPopup();

        // Ambil rekomendasi bandara
        getRecommendations(latitude, longitude);

    });

});
</script>

<script>
// ==========================================
// PENCARIAN HURUF PER HURUF (AUTOCOMPLETE)
// ==========================================
(function () {
    const form = document.getElementById('search-form');
    const input = document.getElementById('search-input');
    const box = document.getElementById('search-suggestions');

    if (!form || !input || !box) return;

    const SUGGEST_URL = @json(route('airports.suggest'));
    const HOME_URL = @json(route('home'));
    const t = {
        noResults: @json(__('messages.welcome_suggest_none')),
        didYouMean: @json(__('messages.search_did_you_mean')),
        seeAll: @json(__('messages.welcome_suggest_see_all')),
        failed: @json(__('messages.welcome_suggest_failed'))
    };

    let timer = null;
    let controller = null;
    let activeIndex = -1;
    let items = [];
    let lastRequest = 0;

    function closeBox() {
        box.classList.add('hidden');
        box.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
        items = [];
    }

    function setMessage(text) {
        box.innerHTML = '';
        const message = document.createElement('div');
        message.className = 'p-4 text-sm text-slate-500 text-center';
        message.textContent = text;
        box.appendChild(message);
        box.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
        items = [];
    }

    // Tebalkan potongan teks yang cocok dengan huruf yang diketik
    function highlight(text, query) {
        const fragment = document.createDocumentFragment();
        const index = text.toLowerCase().indexOf(query.toLowerCase());

        if (index < 0 || !query) {
            fragment.appendChild(document.createTextNode(text));
            return fragment;
        }

        fragment.appendChild(document.createTextNode(text.slice(0, index)));
        const mark = document.createElement('strong');
        mark.className = 'text-[#4A7FB5] bg-[#FFE08A]/50 rounded px-0.5';
        mark.textContent = text.slice(index, index + query.length);
        fragment.appendChild(mark);
        fragment.appendChild(document.createTextNode(text.slice(index + query.length)));

        return fragment;
    }

    function setActive(index) {
        items.forEach(function (item, i) {
            item.classList.toggle('bg-[#A8C6E7]/30', i === index);
            item.setAttribute('aria-selected', i === index ? 'true' : 'false');
        });
        activeIndex = index;

        if (items[index]) {
            items[index].scrollIntoView({ block: 'nearest' });
        }
    }

    function render(data) {
        const query = data.query;
        box.innerHTML = '';
        items = [];

        if (!data.results.length) {
            const empty = document.createElement('div');
            empty.className = 'p-4 text-sm text-slate-500 text-center';
            empty.textContent = t.noResults;

            if (data.suggestion) {
                const link = document.createElement('a');
                link.href = HOME_URL + '?search=' + encodeURIComponent(data.suggestion);
                link.className = 'block mt-2 font-semibold text-[#7FA8D6] underline';
                link.textContent = t.didYouMean + ' ' + data.suggestion;
                empty.appendChild(link);
            }

            box.appendChild(empty);
            box.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
            return;
        }

        data.results.forEach(function (airport) {
            const link = document.createElement('a');
            link.href = airport.url;
            link.setAttribute('role', 'option');
            link.className = 'block px-5 py-3 border-b border-slate-100 hover:bg-[#A8C6E7]/30 transition';

            const name = document.createElement('p');
            name.className = 'font-semibold text-sm text-slate-800';
            name.appendChild(highlight(airport.name, query));

            const detail = document.createElement('p');
            detail.className = 'text-xs text-slate-500 mt-0.5';
            detail.appendChild(highlight(
                [airport.iata || '-', airport.icao || '-', airport.municipality || '-', airport.country || '-'].join(' • '),
                query
            ));

            link.appendChild(name);
            link.appendChild(detail);
            box.appendChild(link);
            items.push(link);
        });

        // Tautan ke hasil lengkap (submit form biasa)
        const all = document.createElement('a');
        all.href = HOME_URL + '?search=' + encodeURIComponent(query);
        all.setAttribute('role', 'option');
        all.className = 'block px-5 py-3 text-sm font-semibold text-[#7FA8D6] hover:bg-[#A8C6E7]/30 transition';
        all.textContent = t.seeAll.replace(':count', data.total).replace(':query', query);
        box.appendChild(all);
        items.push(all);

        activeIndex = -1;
        box.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    }

    async function fetchSuggestions(query) {
        if (controller) controller.abort();
        controller = new AbortController();
        const requestId = ++lastRequest;

        try {
            const response = await fetch(SUGGEST_URL + '?q=' + encodeURIComponent(query), {
                signal: controller.signal,
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('HTTP ' + response.status);

            const data = await response.json();

            // Abaikan respons lama bila pengguna sudah mengetik lagi
            if (requestId !== lastRequest || input.value.trim() !== query) return;

            render(data);
        } catch (error) {
            if (error.name === 'AbortError') return;
            setMessage(t.failed);
        }
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const query = input.value.trim();

        if (!query) {
            lastRequest++;
            closeBox();
            return;
        }

        timer = setTimeout(function () { fetchSuggestions(query); }, 150);
    });

    input.addEventListener('keydown', function (event) {
        if (box.classList.contains('hidden') || !items.length) return;

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive((activeIndex + 1) % items.length);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive((activeIndex - 1 + items.length) % items.length);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            window.location.href = items[activeIndex].href;
        } else if (event.key === 'Escape') {
            closeBox();
        }
    });

    input.addEventListener('focus', function () {
        if (input.value.trim() && !box.children.length) {
            fetchSuggestions(input.value.trim());
        } else if (box.children.length) {
            box.classList.remove('hidden');
        }
    });

    document.addEventListener('click', function (event) {
        if (!form.contains(event.target)) {
            box.classList.add('hidden');
        }
    });
})();
</script>

</body>
</html>