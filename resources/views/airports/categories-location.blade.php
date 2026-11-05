<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Negara ASEAN - AeroSearch</title>
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
                    <span class="text-4xl shadow-sm">{{ $country['flag'] }}</span>
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