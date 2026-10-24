
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>apanamanyamenurutkelen — Pencarian Bandara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">

    <!-- NAVBAR -->
    <nav class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-700 text-2xl text-white">
                    ✈
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">apanamanyamenurutkelen</h1>
                    <p class="text-xs text-slate-500">Airport Discovery</p>
                </div>
            </a>

            <div class="hidden items-center gap-7 text-sm font-medium md:flex">
                <a href="#beranda" class="text-emerald-700">Beranda</a>
                <a href="#fitur" class="hover:text-emerald-700">Fitur</a>
                <a href="#tentang" class="hover:text-emerald-700">Tentang</a>
            </div>

            <a href="#pencarian"
               class="rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Mulai Pencarian
            </a>
        </div>
    </nav>

    <!-- HERO -->
    <main>
        <section id="beranda" class="relative overflow-hidden bg-slate-950">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-emerald-500 opacity-20 blur-3xl"></div>
            <div class="absolute -bottom-32 left-1/3 h-80 w-80 rounded-full bg-cyan-500 opacity-10 blur-3xl"></div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 md:grid-cols-2 md:py-28">
                <div>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-400/10 px-4 py-2 text-sm text-emerald-300">
                        <span>●</span>
                        <span>Eksplorasi bandara lebih mudah</span>
                    </div>

                    <h2 class="max-w-2xl text-4xl font-extrabold leading-tight tracking-tight text-white md:text-6xl">
                        Temukan Bandara
                        <span class="text-emerald-400">Terdekat</span>
                        untuk Perjalananmu.
                    </h2>

                    <p class="mt-6 max-w-xl text-base leading-7 text-slate-300 md:text-lg">
                        ini cuma sekedar contoh, nanti di edit lagi yahhh, orang front-end
                    </p>

                    <div class="mt-9 flex flex-wrap gap-4">
                        <a href="#pencarian"
                           class="rounded-xl bg-emerald-500 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-emerald-400">
                            Jelajahi Bandara →
                        </a>

                        <a href="#fitur"
                           class="rounded-xl border border-slate-600 px-6 py-3.5 font-semibold text-white transition hover:bg-white/10">
                            Pelajari Fitur
                        </a>
                    </div>
                </div>

                <!-- KARTU ILUSTRASI -->
                <div class="relative">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur">
                        <div class="rounded-2xl bg-gradient-to-br from-emerald-800 via-teal-900 to-slate-900 p-7 md:p-10">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-emerald-200">
                                    AIRPORT DISCOVERY
                                </span>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-white">
                                    Indonesia
                                </span>
                            </div>

                            <div class="my-12 text-center">
                                <div class="text-7xl md:text-8xl">✈</div>
                                <p class="mt-5 text-2xl font-bold text-white">
                                    Your Journey Starts Here
                                </p>
                                <p class="mt-2 text-sm text-emerald-100">
                                    Cari. Temukan. Jelajahi.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-white/10 p-4">
                                    <p class="text-sm text-emerald-100">Pencarian</p>
                                    <p class="mt-1 font-semibold text-white">Bandara</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/10 p-4">
                                    <p class="text-sm text-emerald-100">Rekomendasi</p>
                                    <p class="mt-1 font-semibold text-white">Lokasi Terdekat</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PENCARIAN -->
        <section id="pencarian" class="mx-auto -mt-1 max-w-7xl px-6 py-12">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold">Cari Bandara</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Masukkan nama bandara, kota, atau provinsi yang ingin kamu cari.
                    </p>
                </div>

                <form action="#pencarian" method="GET"
                      class="flex flex-col gap-3 md:flex-row">
                    <label for="keyword" class="sr-only">Kata kunci pencarian</label>
                    <input
                        id="keyword"
                        type="search"
                        name="q"
                        placeholder="Contoh: Kualanamu, Medan, Sumatera Utara..."
                        class="min-w-0 flex-1 rounded-xl border border-slate-300 px-5 py-4 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >

                    <button type="submit"
                            class="rounded-xl bg-emerald-700 px-8 py-4 font-semibold text-white transition hover:bg-emerald-800">
                        Cari Bandara
                    </button>
                </form>

                <p class="mt-3 text-xs text-slate-400">
                    Tampilan pencarian awal. Pengolahan hasil pencarian akan ditambahkan berikutnya.
                </p>
            </div>
        </section>

        <!-- FITUR -->
        <section id="fitur" class="mx-auto max-w-7xl px-6 py-14">
            <div class="max-w-2xl">
                <p class="font-semibold uppercase tracking-widest text-emerald-700">
                    Fitur Utama
                </p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight md:text-4xl">
                    Semua informasi bandara dalam satu platform.
                </h2>
                <p class="mt-4 leading-7 text-slate-600">
                    Dirancang untuk mempermudah pencarian dan eksplorasi
                    bandara melalui lokasi geografis dan hubungan data.
                </p>
            </div>

            <div class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                <article class="rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-2xl">
                        📍
                    </div>
                    <h3 class="mt-5 text-xl font-bold">Bandara Terdekat</h3>
                    <p class="mt-3 leading-7 text-slate-600">
                        Temukan bandara berdasarkan lokasi dan urutan jarak.
                    </p>
                    <span class="mt-5 inline-block text-sm font-semibold text-emerald-700">
                        Rekomendasi lokasi →
                    </span>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-2xl">
                        🗺️
                    </div>
                    <h3 class="mt-5 text-xl font-bold">Peta Persebaran</h3>
                    <p class="mt-3 leading-7 text-slate-600">
                        Jelajahi lokasi bandara dan persebarannya di berbagai wilayah.
                    </p>
                    <span class="mt-5 inline-block text-sm font-semibold text-emerald-700">
                        Peta interaktif →
                    </span>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-2xl">
                        🔎
                    </div>
                    <h3 class="mt-5 text-xl font-bold">Pencarian Semantik</h3>
                    <p class="mt-3 leading-7 text-slate-600">
                        Jelajahi informasi berdasarkan hubungan antara bandara dan wilayah.
                    </p>
                    <span class="mt-5 inline-block text-sm font-semibold text-emerald-700">
                        Penelusuran cerdas →
                    </span>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-2xl">
                        🛫
                    </div>
                    <h3 class="mt-5 text-xl font-bold">Detail Bandara</h3>
                    <p class="mt-3 leading-7 text-slate-600">
                        Lihat kode, koordinat, wilayah, dan informasi bandara yang tersedia.
                    </p>
                    <span class="mt-5 inline-block text-sm font-semibold text-emerald-700">
                        Informasi bandara →
                    </span>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-100 text-2xl">
                        🎯
                    </div>
                    <h3 class="mt-5 text-xl font-bold">Filter Pencarian</h3>
                    <p class="mt-3 leading-7 text-slate-600">
                        Saring hasil berdasarkan wilayah dan jenis bandara.
                    </p>
                    <span class="mt-5 inline-block text-sm font-semibold text-emerald-700">
                        Filter hasil →
                    </span>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-100 text-2xl">
                        💾
                    </div>
                    <h3 class="mt-5 text-xl font-bold">Favorit Pengguna</h3>
                    <p class="mt-3 leading-7 text-slate-600">
                        Simpan bandara pilihan setelah fitur akun pengguna tersedia.
                    </p>
                    <span class="mt-5 inline-block text-sm font-semibold text-emerald-700">
                        Fitur pengembangan →
                    </span>
                </article>

            </div>
        </section>

        <!-- TENTANG -->
        <section id="tentang" class="bg-emerald-950 text-white">
            <div class="mx-auto max-w-7xl px-6 py-14 md:py-16">
                <p class="text-sm font-semibold uppercase tracking-widest text-emerald-300">
                    Tentang AeroFind
                </p>
                <h2 class="mt-3 max-w-2xl text-3xl font-bold">
                    Eksplorasi bandara dengan pencarian berbasis lokasi dan semantik.
                </h2>
                <p class="mt-5 max-w-3xl leading-7 text-emerald-100/80">
                    AeroFind merupakan konsep platform pencarian dan rekomendasi
                    bandara. Pengembangan selanjutnya akan mengintegrasikan data
                    bandara, basis data, peta digital, serta teknologi Web Semantik.
                </p>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-950 px-6 py-6 text-center text-sm text-slate-400">
        © {{ date('Y') }} AeroFind · Airport Discovery Platform
    </footer>

</body>
</html>
