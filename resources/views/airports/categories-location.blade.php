<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Negara ASEAN - AeroSearch</title>
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
    <main class="w-full max-w-5xl mx-auto px-6 py-16 flex-grow">
        <!-- Tombol Kembali -->
        <div class="mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-semibold text-[#7FA8D6] hover:underline">
                &larr; Kembali ke Beranda
            </a>
        </div>

        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-[#7FA8D6] mb-3">Direktori Negara ASEAN</h1>
            <p class="text-slate-600 text-base">Pilih salah satu negara di bawah ini untuk melihat daftar lengkap bandara yang tersedia.</p>
        </div>

        <!-- Grid Pilihan Negara -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($aseanCountries as $country)
                <a href="{{ route('home', ['search' => $country['name']]) }}" 
                   class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border-2 border-white shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 flex items-center gap-4 group">
                    <span class="w-12 h-12 rounded-2xl bg-[#A8C6E7]/30 text-[#7FA8D6] font-bold text-base flex items-center justify-center shrink-0">
                        {{ $country['code'] }}
                    </span>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 group-hover:text-[#7FA8D6] transition-colors">{{ $country['name'] }}</h3>
                        <p class="text-xs text-slate-500">Kode ISO: {{ $country['code'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </main>

    <!-- Footer -->
    @include('layouts.footer')

</body>
</html>