<!-- Navbar -->
<nav class="w-full bg-[#7FA8D6]/90 backdrop-blur-md py-4 px-8 flex justify-between items-center shadow-md sticky top-0 z-40">
    <a href="{{ route('home') }}"
       class="flex items-center gap-3 cursor-pointer hover:scale-105 transition-transform duration-300">

        <img src="{{ asset('images/icon.svg') }}"
             alt="Logo AeroFind"
             class="h-10 w-10 object-contain">

        <div class="text-white font-bold text-2xl tracking-wide cursor-pointer hover:scale-105 transition-transform duration-300">
            Aero<span class="text-[#FFE08A]">{{ __('messages.navbar_brand_sub') }}</span>
        </div>

    </a>

    <div class="flex items-center space-x-6 text-white font-semibold">
        @if(request()->routeIs('chatbot'))
            <!-- Special button when on Chatbot page -->
            <a href="{{ route('home') }}"
               class="bg-white/80 hover:bg-white text-[#7FA8D6] px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('messages.navbar_back_home') }}
            </a>
        @else
            <!-- Standard menu on Welcome / Home page -->
            <a href="{{ route('home') }}" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">{{ __('messages.navbar_home') }}</a>
            <a href="{{ route('navigation.index') }}" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">{{ __('messages.navbar_map') }}</a>
            <a href="{{ route('airports.directory') }}" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">{{ __('messages.navbar_directory') }}</a>
        @endif

        <!-- Language Switcher ID / EN -->
        <div class="flex items-center gap-1 ml-2 bg-white/20 rounded-full p-1">
            <a href="{{ route('lang.switch', 'id') }}"
               class="px-3 py-1 rounded-full text-sm font-bold transition-all duration-200 {{ app()->getLocale() === 'id' ? 'bg-[#FFE08A] text-slate-700 shadow-sm' : 'text-white hover:bg-white/20' }}">
                ID
            </a>
            <a href="{{ route('lang.switch', 'en') }}"
               class="px-3 py-1 rounded-full text-sm font-bold transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-[#FFE08A] text-slate-700 shadow-sm' : 'text-white hover:bg-white/20' }}">
                EN
            </a>
        </div>
    </div>
</nav>
