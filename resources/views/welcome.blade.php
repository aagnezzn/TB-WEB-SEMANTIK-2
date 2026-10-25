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
    <nav class="w-full bg-[#7FA8D6]/90 backdrop-blur-md py-4 px-8 flex justify-between items-center shadow-md sticky top-0 z-40">
        <div class="text-white font-bold text-2xl tracking-wide cursor-pointer hover:scale-105 transition-transform duration-300">
            Aero<span class="text-[#FFE08A]">Search</span>
        </div>
        <div class="flex space-x-6 text-white font-semibold">
            <a href="#" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">Beranda</a>
            <a href="#peta" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">Peta Interaktif</a>
            <a href="#" class="hover:text-[#FFE08A] hover:-translate-y-1 transition-all duration-300">Data Bandara</a>
        </div>
    </nav>

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
    <button class="fixed bottom-8 right-8 bg-[#FFE08A] hover:bg-[#FFF2B2] border-4 border-white text-slate-800 rounded-full w-16 h-16 flex justify-center items-center shadow-2xl transition-all duration-300 hover:scale-110 hover:-translate-y-2 active:scale-95 z-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>

    <!-- Footer -->
    <footer class="w-full bg-[#7FA8D6] py-8 text-center text-white mt-auto">
        <div class="max-w-4xl mx-auto px-4">
            <h3 class="text-xl font-bold mb-2 hover:text-[#FFE08A] transition-colors duration-300 cursor-pointer">AeroSearch - Web Semantik 2</h3>
            <p class="text-sm text-white/80 mb-4">Proyek pencarian semantik untuk data bandara di Asia Tenggara.</p>
            <div class="border-t border-white/30 pt-4 text-xs text-white/70">
                &copy; 2026 Kelompok Web Semantik. Universitas Sumatera Utara.
            </div>
        </div>
    </footer>

</body>
</html>