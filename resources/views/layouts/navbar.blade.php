<!-- Navbar -->
<nav class="w-full bg-[#7FA8D6]/90 backdrop-blur-md py-4 px-8 flex justify-between items-center shadow-md sticky top-0 z-40">
    <a href="{{ route('home') }}"
   class="flex items-center gap-3 cursor-pointer hover:scale-105 transition-transform duration-300">

    <img src="{{ asset('images/icon.svg') }}"
         alt="Logo AeroFind"
         class="h-10 w-10 object-contain">

    <div class="text-white font-bold text-2xl tracking-wide cursor-pointer hover:scale-105 transition-transform duration-300">
        Aero<span class="text-[#FFE08A]">Search</span>
    </div>

    </a>

    <div class="flex items-center space-x-6 text-white font-semibold">
        @if(request()->routeIs('chatbot'))
            <!-- Tombol khusus kalau lagi di halaman Chatbot -->
            <a href="{{ route('home') }}"
               class="bg-white/80 hover:bg-white text-[#7FA8D6] px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Beranda
            </a>
        @else
            <!-- Menu standar kalau lagi di halaman Welcome / Beranda -->
            <a href="{{ route('home') }}" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">Beranda</a>
            <a href="#peta" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">Peta Interaktif</a>
            <a href="#" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">Data Bandara</a>
        @endif
    </div>
</nav>