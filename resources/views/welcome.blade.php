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

        <!-- Search Bar -->
        <div class="w-full max-w-3xl relative group">
            <input type="text" 
                class="w-full px-6 py-4 rounded-full border-2 border-white/60 bg-white/80 backdrop-blur-sm focus:outline-none focus:border-[#7FA8D6] focus:ring-4 focus:ring-[#A8C6E7]/50 text-lg shadow-xl transition-all duration-300 group-hover:shadow-2xl"
                placeholder="Cari lokasi, status, atau kontak bandara ASEAN...">
            
            <button class="absolute right-2 top-2 bottom-2 bg-[#FFE08A] hover:bg-[#FFF2B2] text-slate-700 font-bold px-8 rounded-full transition-all duration-300 hover:scale-105 hover:shadow-lg active:scale-95">
                Cari
            </button>
        </div>

        <!-- Sugesti Pencarian -->
        <div class="flex space-x-3 mt-6 text-sm items-center">
            <span class="text-slate-500 font-medium py-1">Kategori Data:</span>
            <a href="#" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">Lokasi & Alamat</a>
            <a href="#" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">Nomor Telepon</a>
            <a href="#" class="bg-white/60 border border-[#A8C6E7] text-[#7FA8D6] px-4 py-1.5 rounded-full hover:bg-[#7FA8D6] hover:text-white hover:scale-105 transition-all duration-300 shadow-sm hover:shadow-md">Status Operasional</a>
        </div>
    </main>

    <!-- Google Maps Section -->
    <section id="peta" class="w-full py-16 px-8 flex-grow">
        <div class="max-w-6xl mx-auto flex flex-col items-center">
            <h2 class="text-3xl font-bold text-[#7FA8D6] mb-6">Peta Lokasi Bandara</h2>
            <div class="w-full h-[400px] bg-white/50 backdrop-blur-md rounded-3xl flex justify-center items-center border-4 border-white shadow-xl relative overflow-hidden transition-all duration-500 hover:shadow-2xl hover:bg-white/60 group">
                <p class="text-[#7FA8D6] font-semibold text-xl z-10 group-hover:scale-110 transition-transform duration-500">Google Maps Terintegrasi Di Sini</p>
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

</body>
</html>