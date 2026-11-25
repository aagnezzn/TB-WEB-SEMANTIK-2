<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotNlpService
{
    /**
- Kamus normalisasi singkatan, bahasa santai, dan typo umum dalam percakapan chat Indonesia.
     *
- @var array<string, string>
     */
    protected array $slangDictionary = [
        // Singkatan umum & kata ganti
        'yg'          => 'yang',
        'yng'         => 'yang',
        'yangg'       => 'yang',
        'gw'          => 'saya',
        'gue'         => 'saya',
        'gua'         => 'saya',
        'lu'          => 'kamu',
        'lo'          => 'kamu',
        'elu'         => 'kamu',
        'sy'          => 'saya',
        'km'          => 'kamu',
        'kmu'         => 'kamu',

        // Kata tanya & preposisi
        'dmn'         => 'di mana',
        'dmna'        => 'di mana',
        'dimn'        => 'di mana',
        'dimna'       => 'di mana',
        'mn'          => 'mana',
        'gmn'         => 'bagaimana',
        'gmana'       => 'bagaimana',
        'bgm'         => 'bagaimana',
        'brp'         => 'berapa',
        'brapa'       => 'berapa',
        'knp'         => 'kenapa',
        'ap'          => 'apa',
        'apkh'        => 'apakah',
        'apaan'       => 'apa',
        'd'           => 'di',
        'dr'          => 'dari',
        'dri'         => 'dari',
        'k'           => 'ke',
        'dgn'         => 'dengan',
        'dg'          => 'dengan',
        'sm'          => 'sama',
        'sma'         => 'sama',
        'klo'         => 'kalau',
        'kalo'        => 'kalau',
        'kl'          => 'kalau',
        'krn'         => 'karena',
        'tp'          => 'tapi',
        'tpi'         => 'tapi',

        // Kata bantu & partikel
        'aj'          => 'saja',
        'aja'         => 'saja',
        'sjo'         => 'saja',
        'bgt'         => 'banget',
        'bngt'        => 'banget',
        'bgtu'        => 'begitu',
        'trus'        => 'terus',
        'trs'         => 'terus',
        'bs'          => 'bisa',
        'udh'         => 'sudah',
        'udah'        => 'sudah',
        'blm'         => 'belum',
        'blom'        => 'belum',
        'gak'         => 'tidak',
        'gk'          => 'tidak',
        'ga'          => 'tidak',
        'ngga'        => 'tidak',
        'nggak'       => 'tidak',
        'tdk'         => 'tidak',
        'tau'         => 'tahu',
        'tw'          => 'tahu',
        'tlg'         => 'tolong',
        'tlng'        => 'tolong',
        'bantu'       => 'tolong',
        'plis'        => 'tolong',
        'pls'         => 'tolong',

        // Kata spesifik bandara, lokasi & jarak
        'bndara'      => 'bandara',
        'bandaraa'    => 'bandara',
        'bndr'        => 'bandara',
        'aprt'        => 'bandara',
        'airport'     => 'bandara',
        'aeroport'    => 'bandara',
        'dkat'        => 'dekat',
        'dket'        => 'dekat',
        'deket'       => 'dekat',
        'deketnya'    => 'dekat',
        'dekett'      => 'dekat',
        'terdkat'     => 'terdekat',
        'terdkt'      => 'terdekat',
        'plg'         => 'paling',
        'palingg'     => 'paling',
        'daerh'       => 'daerah',
        'daera'       => 'daerah',
        'wil'         => 'wilayah',
        'kab'         => 'kabupaten',
        'kab.'        => 'kabupaten',
        'kot'         => 'kota',
        'kta'         => 'kota',
        'rekomen'     => 'rekomendasi',
        'recomen'     => 'rekomendasi',
        'rekomended'  => 'rekomendasi',
        'rekomendasiin' => 'rekomendasi',
        'inpo'        => 'informasi',
        'infony'      => 'informasi',
        'infonya'     => 'informasi',
        'skitar'      => 'sekitar',
        'sekitr'      => 'sekitar',
        'lok'         => 'lokasi',
        'lokasiny'    => 'lokasi',
        'posisi'      => 'lokasi',
        'cariin'      => 'cari',
        'carikan'     => 'cari',
        'ksih'        => 'beri',
        'kasih'       => 'beri',
        'jelasin'     => 'jelaskan',
        'ceritain'    => 'ceritakan',

        // Tambahan: fasilitas, perbandingan
        'fsilitas'    => 'fasilitas',
        'fasil'       => 'fasilitas',
        'besar'       => 'large',
        'besar-besar' => 'large',
        'gede'        => 'large',
        'internasional'=> 'large',
        'sedang'      => 'medium',
        'kecil'       => 'small',
        'bandingin'   => 'bandingkan',
        'banding'     => 'bandingkan',
        'lebih dkt'   => 'lebih dekat',
        'lbh dkt'     => 'lebih dekat',
        'lbh dekat'   => 'lebih dekat',
    ];

    /**
- Kata-kata pengisi (filler words) yang diabaikan saat ekstraksi kata kunci nama wilayah.
     *
- @var array<int, string>
     */
    protected array $fillerWords = [
        'bro', 'sob', 'sis', 'min', 'gan', 'cuy', 'dong', 'deh', 'sih', 'kan',
        'tuh', 'nih', 'ya', 'yah', 'kah', 'lah', 'eh', 'woy', 'hei', 'halo',
        'hai', 'hello', 'tolong', 'coba', 'saya', 'aku', 'kamu', 'ingin', 'mau'
    ];

    /**
- Kamus alias kota / kabupaten populer untuk pemetaan nama resmi wilayah geospasial.
     *
- @var array<string, string>
     */
    protected array $regionAliases = [
        'medan'        => 'Kota Medan',
        'bandung'      => 'Kota Bandung',
        'surabaya'     => 'Kota Surabaya',
        'sby'          => 'Kota Surabaya',
        'jogja'        => 'Kota Yogyakarta',
        'yogya'        => 'Kota Yogyakarta',
        'yogyakarta'   => 'Kota Yogyakarta',
        'yk'           => 'Kota Yogyakarta',
        'jakarta'      => 'Kota Jakarta Pusat',
        'jkt'          => 'Kota Jakarta Pusat',
        'jaksel'       => 'Kota Jakarta Selatan',
        'jakpus'       => 'Kota Jakarta Pusat',
        'jaktim'       => 'Kota Jakarta Timur',
        'jakbar'       => 'Kota Jakarta Barat',
        'jakut'        => 'Kota Jakarta Utara',
        'bali'         => 'Badung', // Bandara I Gusti Ngurah Rai secara administratif berada di Kab. Badung
        'denpasar'     => 'Kota Denpasar',
        'deli serdang' => 'Deli Serdang',
        'deliserdang'  => 'Deli Serdang',
        'semarang'     => 'Kota Semarang',
        'makassar'     => 'Kota Makassar',
        'palembang'    => 'Kota Palembang',
        'padang'       => 'Kota Padang',
        'pekanbaru'    => 'Kota Pekanbaru',
        'pku'          => 'Kota Pekanbaru',
        'batam'        => 'Kota Batam',
        'malang'       => 'Kota Malang',
        'solo'         => 'Kota Surakarta',
        'surakarta'    => 'Kota Surakarta',
        'balikpapan'   => 'Kota Balikpapan',
        'samarinda'    => 'Kota Samarinda',
        'pontianak'    => 'Kota Pontianak',
        'banjarmasin'  => 'Kota Banjarmasin',
        'manado'       => 'Kota Manado',
        'mataram'      => 'Kota Mataram',
        'lombok'       => 'Lombok Tengah',
        'labuan bajo'  => 'Manggarai Barat',
        'kupang'       => 'Kota Kupang',
        'jayapura'     => 'Kota Jayapura',
        'ambon'        => 'Kota Ambon',
        'aceh'         => 'Kota Banda Aceh',
        'banda aceh'   => 'Kota Banda Aceh',
        'kl'           => 'Kuala Lumpur',
        'kuala lumpur' => 'Kuala Lumpur',
        'singapore'    => 'Singapore',
        'singapura'    => 'Singapore',
        'sg'           => 'Singapore',
        'bangkok'      => 'Bangkok',
        'manila'       => 'Manila',
    ];

    /**
- Titik koordinat wilayah populer untuk resolusi instan geospasial.
     *
- @var array<string, array{lat: float, lng: float, country?: string}>
     */
    protected array $regionCoordinates = [
        'medan'        => ['lat' => 3.642342, 'lng' => 98.6761353, 'country' => 'ID'],
        'bandung'      => ['lat' => -6.9174639, 'lng' => 107.6191228, 'country' => 'ID'],
        'surabaya'     => ['lat' => -7.2574719, 'lng' => 112.7520883, 'country' => 'ID'],
        'sby'          => ['lat' => -7.2574719, 'lng' => 112.7520883, 'country' => 'ID'],
        'jogja'        => ['lat' => -7.7955798, 'lng' => 110.3694896, 'country' => 'ID'],
        'yogya'        => ['lat' => -7.7955798, 'lng' => 110.3694896, 'country' => 'ID'],
        'yogyakarta'   => ['lat' => -7.7955798, 'lng' => 110.3694896, 'country' => 'ID'],
        'yk'           => ['lat' => -7.7955798, 'lng' => 110.3694896, 'country' => 'ID'],
        'jakarta'      => ['lat' => -6.1824343, 'lng' => 106.8428635, 'country' => 'ID'],
        'jkt'          => ['lat' => -6.1824343, 'lng' => 106.8428635, 'country' => 'ID'],
        'jaksel'       => ['lat' => -6.2614927, 'lng' => 106.8105998, 'country' => 'ID'],
        'jakpus'       => ['lat' => -6.1824343, 'lng' => 106.8428635, 'country' => 'ID'],
        'jaktim'       => ['lat' => -6.2250138, 'lng' => 106.9004472, 'country' => 'ID'],
        'jakbar'       => ['lat' => -6.1613131, 'lng' => 106.7439336, 'country' => 'ID'],
        'jakut'        => ['lat' => -6.1384144, 'lng' => 106.8640323, 'country' => 'ID'],
        'bali'         => ['lat' => -8.5446241, 'lng' => 115.198283, 'country' => 'ID'],
        'denpasar'     => ['lat' => -8.6704582, 'lng' => 115.2126293, 'country' => 'ID'],
        'deli serdang' => ['lat' => 3.4287515, 'lng' => 98.6761353, 'country' => 'ID'],
        'deliserdang'  => ['lat' => 3.4287515, 'lng' => 98.6761353, 'country' => 'ID'],
        'semarang'     => ['lat' => -7.0051453, 'lng' => 110.4381254, 'country' => 'ID'],
        'makassar'     => ['lat' => -5.1476651, 'lng' => 119.4327316, 'country' => 'ID'],
        'palembang'    => ['lat' => -2.9760735, 'lng' => 104.7754307, 'country' => 'ID'],
        'padang'       => ['lat' => -0.9470832, 'lng' => 100.417181, 'country' => 'ID'],
        'pekanbaru'    => ['lat' => 0.5070677, 'lng' => 101.4477792, 'country' => 'ID'],
        'pku'          => ['lat' => 0.5070677, 'lng' => 101.4477792, 'country' => 'ID'],
        'batam'        => ['lat' => 1.045626, 'lng' => 104.0304535, 'country' => 'ID'],
        'malang'       => ['lat' => -7.9666204, 'lng' => 112.6326321, 'country' => 'ID'],
        'solo'         => ['lat' => -7.5754887, 'lng' => 110.8243272, 'country' => 'ID'],
        'surakarta'    => ['lat' => -7.5754887, 'lng' => 110.8243272, 'country' => 'ID'],
        'balikpapan'   => ['lat' => -1.2379274, 'lng' => 116.8528526, 'country' => 'ID'],
        'samarinda'    => ['lat' => -0.502106, 'lng' => 117.153709, 'country' => 'ID'],
        'pontianak'    => ['lat' => -0.0263303, 'lng' => 109.3425036, 'country' => 'ID'],
        'banjarmasin'  => ['lat' => -3.316694, 'lng' => 114.590111, 'country' => 'ID'],
        'manado'       => ['lat' => 1.4748305, 'lng' => 124.8420794, 'country' => 'ID'],
        'mataram'      => ['lat' => -8.5798725, 'lng' => 116.09949, 'country' => 'ID'],
        'lombok'       => ['lat' => -8.705833, 'lng' => 116.276389, 'country' => 'ID'],
        'labuan bajo'  => ['lat' => -8.496389, 'lng' => 119.887778, 'country' => 'ID'],
        'kupang'       => ['lat' => -10.1771997, 'lng' => 123.6070329, 'country' => 'ID'],
        'jayapura'     => ['lat' => -2.591603, 'lng' => 140.669006, 'country' => 'ID'],
        'ambon'        => ['lat' => -3.6953935, 'lng' => 128.181412, 'country' => 'ID'],
        'aceh'         => ['lat' => 5.5482904, 'lng' => 95.3237559, 'country' => 'ID'],
        'banda aceh'   => ['lat' => 5.5482904, 'lng' => 95.3237559, 'country' => 'ID'],
        'kl'           => ['lat' => 3.139003, 'lng' => 101.686855, 'country' => 'MY'],
        'kuala lumpur' => ['lat' => 3.139003, 'lng' => 101.686855, 'country' => 'MY'],
        'singapore'    => ['lat' => 1.352083, 'lng' => 103.819836, 'country' => 'SG'],
        'singapura'    => ['lat' => 1.352083, 'lng' => 103.819836, 'country' => 'SG'],
        'sg'           => ['lat' => 1.352083, 'lng' => 103.819836, 'country' => 'SG'],
        'bangkok'      => ['lat' => 13.7563309, 'lng' => 100.5017651, 'country' => 'TH'],
        'manila'       => ['lat' => 14.5995124, 'lng' => 120.9842195, 'country' => 'PH'],
    ];

    /**
- Kamus alias bandara dan kode IATA untuk ekstraksi instan.
     *
- @var array<string, array{name: string, iata: string, municipality?: string}>
     */
    protected array $airportAliases = [
        'kualanamu'      => ['name' => 'Kualanamu International Airport', 'iata' => 'KNO', 'municipality' => 'Deli Serdang'],
        'kno'            => ['name' => 'Kualanamu International Airport', 'iata' => 'KNO', 'municipality' => 'Deli Serdang'],
        'soekarno hatta' => ['name' => 'Soekarno-Hatta International Airport', 'iata' => 'CGK', 'municipality' => 'Tangerang'],
        'soekarno-hatta' => ['name' => 'Soekarno-Hatta International Airport', 'iata' => 'CGK', 'municipality' => 'Tangerang'],
        'soetta'         => ['name' => 'Soekarno-Hatta International Airport', 'iata' => 'CGK', 'municipality' => 'Tangerang'],
        'soeta'          => ['name' => 'Soekarno-Hatta International Airport', 'iata' => 'CGK', 'municipality' => 'Tangerang'],
        'cgk'            => ['name' => 'Soekarno-Hatta International Airport', 'iata' => 'CGK', 'municipality' => 'Tangerang'],
        'halim'          => ['name' => 'Halim Perdanakusuma International Airport', 'iata' => 'HLP', 'municipality' => 'Jakarta'],
        'hlp'            => ['name' => 'Halim Perdanakusuma International Airport', 'iata' => 'HLP', 'municipality' => 'Jakarta'],
        'juanda'         => ['name' => 'Juanda International Airport', 'iata' => 'SUB', 'municipality' => 'Sidoarjo'],
        'sub'            => ['name' => 'Juanda International Airport', 'iata' => 'SUB', 'municipality' => 'Sidoarjo'],
        'ngurah rai'     => ['name' => 'I Gusti Ngurah Rai International Airport', 'iata' => 'DPS', 'municipality' => 'Badung / Denpasar'],
        'ngurahrai'      => ['name' => 'I Gusti Ngurah Rai International Airport', 'iata' => 'DPS', 'municipality' => 'Badung / Denpasar'],
        'dps'            => ['name' => 'I Gusti Ngurah Rai International Airport', 'iata' => 'DPS', 'municipality' => 'Badung / Denpasar'],
        'yia'            => ['name' => 'Yogyakarta International Airport', 'iata' => 'YIA', 'municipality' => 'Kulon Progo'],
        'kulon progo'    => ['name' => 'Yogyakarta International Airport', 'iata' => 'YIA', 'municipality' => 'Kulon Progo'],
        'adisucipto'     => ['name' => 'Adisutjipto International Airport', 'iata' => 'JOG', 'municipality' => 'Sleman / Yogyakarta'],
        'adisutjipto'    => ['name' => 'Adisutjipto International Airport', 'iata' => 'JOG', 'municipality' => 'Sleman / Yogyakarta'],
        'jog'            => ['name' => 'Adisutjipto International Airport', 'iata' => 'JOG', 'municipality' => 'Sleman / Yogyakarta'],
        'minangkabau'    => ['name' => 'Minangkabau International Airport', 'iata' => 'PDG', 'municipality' => 'Padang Pariaman'],
        'pdg'            => ['name' => 'Minangkabau International Airport', 'iata' => 'PDG', 'municipality' => 'Padang Pariaman'],
        'hang nadim'     => ['name' => 'Hang Nadim International Airport', 'iata' => 'BTH', 'municipality' => 'Batam'],
        'bth'            => ['name' => 'Hang Nadim International Airport', 'iata' => 'BTH', 'municipality' => 'Batam'],
        'supadio'        => ['name' => 'Supadio International Airport', 'iata' => 'PNK', 'municipality' => 'Kubu Raya / Pontianak'],
        'pnk'            => ['name' => 'Supadio International Airport', 'iata' => 'PNK', 'municipality' => 'Kubu Raya / Pontianak'],
        'hasanuddin'     => ['name' => 'Sultan Hasanuddin International Airport', 'iata' => 'UPG', 'municipality' => 'Maros / Makassar'],
        'upg'            => ['name' => 'Sultan Hasanuddin International Airport', 'iata' => 'UPG', 'municipality' => 'Maros / Makassar'],
        'sam ratulangi'  => ['name' => 'Sam Ratulangi International Airport', 'iata' => 'MDC', 'municipality' => 'Manado'],
        'mdc'            => ['name' => 'Sam Ratulangi International Airport', 'iata' => 'MDC', 'municipality' => 'Manado'],
        'changi'         => ['name' => 'Singapore Changi Airport', 'iata' => 'SIN', 'municipality' => 'Singapore'],
        'sin'            => ['name' => 'Singapore Changi Airport', 'iata' => 'SIN', 'municipality' => 'Singapore'],
        'klia'           => ['name' => 'Kuala Lumpur International Airport', 'iata' => 'KUL', 'municipality' => 'Sepang / Kuala Lumpur'],
        'kul'            => ['name' => 'Kuala Lumpur International Airport', 'iata' => 'KUL', 'municipality' => 'Sepang / Kuala Lumpur'],
    ];

    /**
- Normalisasi teks pesan input: ubah huruf kecil, bersihkan tanda baca berlebih,
- serta ekspansi kata santai, singkatan, dan typo ke bentuk standar.
     */
    public function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));

        // Hilangkan tanda baca tidak esensial
        $text = preg_replace('/[?!.,;:_"\`\'~@#$%^&*()+=<>{}\[\]\/\x{0080}-\x{FFFF}]+/u', ' ', $text);

        // Pisahkan token kata
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (!$words) {
            return '';
        }

        $normalizedWords = [];
        foreach ($words as $word) {
            $normalizedWords[] = $this->slangDictionary[$word] ?? $word;
        }

        return trim(implode(' ', $normalizedWords));
    }

    /**
- Ekstraksi entitas bandara dari teks (nama bandara, kode IATA, atau alias).
     *
- @return array{name: string, iata: string, municipality?: string}|null
     */
    public function extractAirportEntity(string $normalizedMessage): ?array
    {
        $aliases = $this->airportAliases;
        uksort($aliases, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($aliases as $alias => $data) {
            $pattern = '/(?<![\p{L}\p{N}])' . preg_quote($alias, '/') . '(?![\p{L}\p{N}])/iu';
            if (preg_match($pattern, $normalizedMessage)) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Mengenali nama pulau dari pesan pengguna. Alias terpanjang diprioritaskan.
     *
     * @param  list<string>  $dataIslands  Nama pulau yang ada di dataset
     */
    public function extractIslandEntity(string $rawMessage, string $normalizedMessage, array $dataIslands = []): ?string
    {
        $aliases = \App\Services\IslandResolver::aliasMap($dataIslands);
        uksort($aliases, fn ($a, $b) => mb_strlen((string) $b) <=> mb_strlen((string) $a));

        foreach ([mb_strtolower($rawMessage), $normalizedMessage] as $text) {
            foreach ($aliases as $alias => $island) {
                $pattern = '/(?<![\p{L}\p{N}])' . preg_quote((string) $alias, '/') . '(?![\p{L}\p{N}])/iu';
                if (preg_match($pattern, $text)) {
                    return $island;
                }
            }
        }

        return null;
    }

    /**
     * Apakah bahasa aktif aplikasi adalah Inggris.
     */
    protected function isEnglish(): bool
    {
        return app()->getLocale() === 'en';
    }

    /**
     * Respon daftar bandara di sebuah pulau.
     *
     * @param  list<array{name: string, iata: ?string, icao: ?string, municipality: ?string}>  $airports
     */
    public function formatIslandAirports(string $island, array $airports, int $limit = 20): string
    {
        $total = count($airports);
        $isEn = $this->isEnglish();

        if ($total === 0) {
            return $isEn
                ? "No airport data found in {$island} in the AeroFind dataset."
                : "Belum ada data bandara di {$island} pada dataset AeroFind.";
        }

        $reply = ($isEn ? "AIRPORTS IN " : "BANDARA DI ") . mb_strtoupper($island) . "\n\n";
        if ($isEn) {
            $reply .= "There are {$total} airports in {$island}" . ($total > $limit ? ", here are the first {$limit}:" : ":") . "\n\n";
        } else {
            $reply .= "Terdapat {$total} bandara di {$island}" . ($total > $limit ? ", berikut {$limit} pertama:" : ":") . "\n\n";
        }

        foreach (array_slice($airports, 0, $limit) as $index => $airport) {
            $iata = $airport['iata'] ?: '-';
            $loc = $airport['municipality'] ?: '-';
            $reply .= ($index + 1) . ". {$airport['name']} (IATA: {$iata}) - {$loc}\n";
        }

        if ($isEn) {
            $reply .= "\nYou can ask about the profile of one of the airports above, for example: 'Profile {$airports[0]['name']}'.\n";
            $reply .= "Data source: AeroFind RDF Dataset (af:island property) via Apache Jena Fuseki.";
        } else {
            $reply .= "\nKamu dapat menanyakan profil salah satu bandara di atas, contohnya: 'Profil {$airports[0]['name']}'.\n";
            $reply .= "Sumber data: Dataset RDF AeroFind (properti af:island) melalui Apache Jena Fuseki.";
        }

        return $reply;
    }

    /**
     * Ekstraksi filter tipe/ukuran fasilitas bandara dari pesan pengguna (large, medium, small).
     */
    public function extractAirportTypeEntity(string $normalizedMessage): ?string
    {
        if (str_contains($normalizedMessage, 'large') || str_contains($normalizedMessage, 'besar')) {
            return 'large_airport';
        }
        if (str_contains($normalizedMessage, 'medium') || str_contains($normalizedMessage, 'sedang')) {
            return 'medium_airport';
        }
        if (str_contains($normalizedMessage, 'small') || str_contains($normalizedMessage, 'kecil')) {
            return 'small_airport';
        }
        return null;
    }

    /**
- Ekstraksi entitas wilayah (Kota/Kabupaten) secara akurat dari kalimat bebas pengguna.
- Mengembalikan model Region dengan koordinat geospasial dinamis (latitude & longitude)
- tanpa pernah kembali ke titik default statis (seperti Sumedang).
     */
    public function extractRegionEntity(string $rawMessage, string $normalizedMessage): ?Region
    {
        // 1. Cek pemetaan alias cepat (misal: "medan" -> "Kota Medan", "jogja" -> "Kota Yogyakarta", "bali" -> "Badung")
        foreach ($this->regionAliases as $alias => $officialName) {
            $pattern = '/(?<![\p{L}\p{N}])' . preg_quote($alias, '/') . '(?![\p{L}\p{N}])/iu';
            if (preg_match($pattern, $normalizedMessage)) {
                try {
                    $region = Region::where('region_name', $officialName)->first();
                    if ($region) {
                        return $region;
                    }
                } catch (\Throwable $e) {
                    // Fallback jika database belum aktif atau koneksi terhambat
                }

                if (isset($this->regionCoordinates[$alias])) {
                    $coords = $this->regionCoordinates[$alias];
                    return new Region([
                        'region_id'    => 'ALIAS-' . strtoupper($alias),
                        'region_name'  => $officialName,
                        'country_code' => $coords['country'] ?? 'ID',
                        'latitude'     => $coords['lat'],
                        'longitude'    => $coords['lng'],
                    ]);
                }
            }
        }

        // 2. Bersihkan kata instruksi/pengisi agar tersisa kandidat nama wilayah
        $cleanCandidates = $normalizedMessage;
        $stripWords = array_merge(
            $this->fillerWords,
            [
                'bandara', 'airport', 'terdekat', 'dekat', 'paling', 'rekomendasi',
                'informasi', 'profil', 'profilnya', 'apa', 'saja', 'ada', 'yang',
                'dari', 'di', 'ke', 'sekitar', 'daerah', 'wilayah', 'kabupaten',
                'kab', 'kota', 'cari', 'carikan', 'lihat', 'mana', 'dimana',
                'berapa', 'daftar', 'status', 'layanan', 'operasional', 'fasilitas',
                'large', 'medium', 'small', 'besar', 'sedang', 'kecil'
            ]
        );

        foreach ($stripWords as $word) {
            $cleanCandidates = preg_replace('/\b' . preg_quote($word, '/') . '\b/iu', ' ', $cleanCandidates);
        }

        $cleanCandidates = trim(preg_replace('/\s+/', ' ', $cleanCandidates));

        // 3. Jika tersisa kandidat kata spesifik (misal: "medan", "deli serdang", "sumedang")
        if ($cleanCandidates !== '') {
            try {
                // Cek pencarian eksak pada nama wilayah
                $region = Region::whereRaw('LOWER(region_name) = ?', [mb_strtolower($cleanCandidates)])->first();
                if ($region) {
                    return $region;
                }

                // Cek jika wilayah berawalan "Kota [Candidate]" atau "Kabupaten [Candidate]"
                $region = Region::whereRaw('LOWER(region_name) = ?', ['kota ' . mb_strtolower($cleanCandidates)])
                    ->orWhereRaw('LOWER(region_name) = ?', ['kabupaten ' . mb_strtolower($cleanCandidates)])
                    ->first();
                if ($region) {
                    return $region;
                }

                // Cek pencarian LIKE sebagian
                $region = Region::where('region_name', 'LIKE', '%' . $cleanCandidates . '%')
                    ->orderByRaw('LENGTH(region_name) ASC')
                    ->first();
                if ($region) {
                    return $region;
                }
            } catch (\Throwable $e) {
                // Fallback pencarian langsung ke CSV jika MySQL tidak dapat dihubungi
            }

            $csvRegion = $this->findRegionFromCsv($cleanCandidates);
            if ($csvRegion) {
                return $csvRegion;
            }
        }

        // 4. Pencarian dinamis mencocokkan record database langsung ke dalam pesan pengguna
        try {
            $regions = Region::query()
                ->select(['region_id', 'region_name', 'country_code', 'latitude', 'longitude'])
                ->get();

            if ($regions->isNotEmpty()) {
                $sortedRegions = $regions->sortByDesc(fn ($r) => mb_strlen($r->region_name));

                foreach ($sortedRegions as $r) {
                    $rawRegionName = mb_strtolower(trim($r->region_name));
                    $cleanedRegionName = trim(preg_replace('/^(kota|kabupaten|kab\.)\s+/iu', '', $rawRegionName));

                    if (mb_strlen($cleanedRegionName) < 3) {
                        continue;
                    }

                    $patternClean = '/(?<![\p{L}\p{N}])' . preg_quote($cleanedRegionName, '/') . '(?![\p{L}\p{N}])/iu';
                    $patternFull  = '/(?<![\p{L}\p{N}])' . preg_quote($rawRegionName, '/') . '(?![\p{L}\p{N}])/iu';

                    if (preg_match($patternFull, $normalizedMessage) || preg_match($patternClean, $normalizedMessage)) {
                        return $r;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan kegagalan database
        }

        // 5. Toleransi Typo Ringan (Levenshtein Distance)
        if ($cleanCandidates !== '' && mb_strlen($cleanCandidates) >= 4) {
            $candidates = explode(' ', $cleanCandidates);
            foreach ($candidates as $candidate) {
                if (mb_strlen($candidate) < 4) {
                    continue;
                }

                foreach ($this->regionAliases as $alias => $officialName) {
                    if (levenshtein($candidate, $alias) <= 1) {
                        if (isset($this->regionCoordinates[$alias])) {
                            $coords = $this->regionCoordinates[$alias];
                            return new Region([
                                'region_id'    => 'ALIAS-' . strtoupper($alias),
                                'region_name'  => $officialName,
                                'country_code' => $coords['country'] ?? 'ID',
                                'latitude'     => $coords['lat'],
                                'longitude'    => $coords['lng'],
                            ]);
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
- Membaca nama wilayah langsung dari file CSV jika database luring.
     */
    protected function findRegionFromCsv(string $search): ?Region
    {
        $path = database_path('seeders/data/regions_southeast_asia.csv');
        if (!file_exists($path)) {
            return null;
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return null;
        }

        $searchLower = mb_strtolower($search);

        while (($row = fgetcsv($handle, 1000, ',', '"', '')) !== false) {
            if (count($row) < 7) {
                continue;
            }

            $name = trim($row[1]);
            $nameLower = mb_strtolower($name);
            $cleanName = trim(preg_replace('/^(kota|kabupaten|kab\.)\s+/iu', '', $nameLower));

            if ($nameLower === $searchLower || $cleanName === $searchLower || str_contains($nameLower, $searchLower)) {
                fclose($handle);
                return new Region([
                    'region_id'    => $row[0],
                    'region_name'  => $name,
                    'country_code' => $row[3],
                    'latitude'     => (float) $row[5],
                    'longitude'    => (float) $row[6],
                ]);
            }
        }

        fclose($handle);
        return null;
    }

    /**
- Klasifikasi niat (Intent Detection) dari kalimat bebas pengguna.
     */
    public function detectIntent(
        string $normalizedMessage,
        ?Region $regionEntity,
        ?array $airportEntity
    ): string {
        $hasNearbyKeyword = $this->containsAny($normalizedMessage, [
            'dekat', 'terdekat', 'paling dekat', 'rekomendasi', 'rekomendasikan',
            'mana bandara', 'ada bandara apa', 'bandara daerah', 'bandara sekitar',
            'bandara di', 'bandara dekat', 'yang dekat', 'yang terdekat',
            'near', 'nearest', 'closest', 'nearby', 'recommendation', 'recommended', 'airports near'
        ]);

        $hasProfileKeyword = $this->containsAny($normalizedMessage, [
            'profil', 'profile', 'informasi', 'info', 'detail', 'data', 'tentang',
            'spesifikasi', 'kode iata', 'kode icao', 'operator', 'elevasi', 'landasan',
            'fasilitas', 'panjang runaway', 'runway', 'layanan terjadwal', 'status layanan',
            'tell me about', 'details about', 'information about', 'info about', 'elevation', 'scheduled service'
        ]);

        $hasCountKeyword = $this->containsAny($normalizedMessage, [
            'berapa jumlah', 'berapa banyak', 'total bandara', 'jumlah bandara',
            'how many', 'total airports', 'number of airports', 'count of airports'
        ]);

        $hasListKeyword = $this->containsAny($normalizedMessage, [
            'daftar bandara', 'semua bandara', 'seluruh bandara', 'list bandara',
            'bandara di indonesia', 'bandara indonesia',
            'airport list', 'airports list', 'all airports', 'airports in'
        ]);

        $hasGreetingKeyword = $this->containsAny($normalizedMessage, [
            'halo', 'hai', 'hello', 'selamat pagi', 'selamat siang', 'selamat sore',
            'selamat malam', 'siapa kamu', 'apa itu aerofind', 'apa itu aerobot', 'kamu siapa',
            'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'who are you'
        ]);

        $hasHelpKeyword = $this->containsAny($normalizedMessage, [
            'bisa apa', 'fitur', 'fungsi', 'cara pakai', 'cara kerja', 'bantuan', 'help',
            'what can you do', 'how to use', 'features'
        ]);

        $hasTypeKeyword = $this->extractAirportTypeEntity($normalizedMessage) !== null;

        if ($hasTypeKeyword) {
            return 'AIRPORT_BY_TYPE';
        }

        // Prioritas Intent:
        // 1. Rekomendasi Bandara Terdekat
        if ($hasNearbyKeyword && $regionEntity) {
            return 'NEAREST_AIRPORT';
        }

        // 2. Profil Bandara
        if ($airportEntity !== null || ($hasProfileKeyword && !$regionEntity)) {
            return 'AIRPORT_PROFILE';
        }

        // 3. Jika pengguna menyebut wilayah dan kata bandara / daerah
        if ($regionEntity && ($this->containsAny($normalizedMessage, ['bandara', 'mana', 'ada apa', 'cari', 'di']) || $hasNearbyKeyword)) {
            return 'NEAREST_AIRPORT';
        }

        // 4. Hitung Jumlah Bandara
        if ($hasCountKeyword) {
            return 'INDONESIA_AIRPORTS_COUNT';
        }

        // 5. Daftar Bandara Indonesia
        if ($hasListKeyword) {
            return 'INDONESIA_AIRPORTS_LIST';
        }

        // 6. Sapaan & Identitas Bot
        if ($hasGreetingKeyword) {
            return 'GREETING';
        }

        // 7. Bantuan / Fitur
        if ($hasHelpKeyword) {
            return 'HELP';
        }

        // 8. Jika hanya menyebut nama wilayah saja
        if ($regionEntity) {
            return 'NEAREST_AIRPORT';
        }

        return 'FALLBACK';
    }

    /**
- Memeriksa apakah teks mengandung salah satu kata kunci.
     *
- @param array<int, string> $keywords
     */
    public function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMATTER RESPON NATURAL, ON-POINT, TANPA EMOJI, MENGGUNAKAN "KAMU"
    |--------------------------------------------------------------------------
    */

    /**
- Format respon rekomendasi bandara terdekat.
     */
    public function formatNearestAirports(Region $region, Collection $airports): string
    {
        $isEn = $this->isEnglish();

        if ($airports->isEmpty()) {
            return $isEn
                ? "Sorry, no active airport data with coordinates found around {$region->region_name}. You can try searching for another city or municipality."
                : "Maaf, belum ditemukan data bandara dengan koordinat aktif di sekitar wilayah {$region->region_name}. Kamu dapat mencoba mencari nama kota atau kabupaten lain.";
        }

        $regionTitle = $region->region_name;
        $lat = number_format((float) $region->latitude, 4);
        $lng = number_format((float) $region->longitude, 4);

        $reply = $isEn
            ? "Here are 5 nearest airports from {$regionTitle} (coordinates: {$lat}, {$lng}):\n\n"
            : "Berikut 5 bandara terdekat dari wilayah {$regionTitle} (koordinat: {$lat}, {$lng}):\n\n";

        foreach ($airports as $index => $airport) {
            $num = $index + 1;
            $dist = number_format((float) $airport->distance_km, 2, ',', '.');
            $iata = $airport->iata_code ?: '-';
            $icao = $airport->icao_code ?: '-';
            $loc = $airport->municipality ?: ($isEn ? 'Around region' : 'Sekitar wilayah');

            $reply .= "{$num}. {$airport->name}\n";
            $reply .= $isEn ? "   - Location: {$loc}\n" : "   - Lokasi: {$loc}\n";
            $reply .= $isEn ? "   - IATA / ICAO Code: {$iata} / {$icao}\n" : "   - Kode IATA / ICAO: {$iata} / {$icao}\n";
            $reply .= $isEn ? "   - Estimated distance: {$dist} km\n\n" : "   - Jarak perkiraan: {$dist} km\n\n";
        }

        $reply .= $isEn
            ? "Note: Distance is calculated straight-line using the Haversine formula from {$regionTitle} center point.\n\nWould you like to see the complete profile of one of the airports above?"
            : "Catatan: Jarak dihitung garis lurus menggunakan rumus Haversine dari titik pusat wilayah {$regionTitle}.\n\nApakah kamu ingin melihat profil lengkap salah satu bandara di atas?";

        return $reply;
    }

    /**
     * - Format respon profil bandara dari Fuseki RDF / DBpedia.
     *
     * @param array<string, mixed> $airport
     */
    public function formatAirportProfile(array $airport): string
    {
        $isEn = $this->isEnglish();
        $notAvail = $isEn ? 'Not available' : 'Tidak tersedia';
        $get = fn ($key) => $airport[$key]['value'] ?? ($airport[$key] ?? $notAvail);

        $name = $get('name');
        $iata = $get('iataCode') ?: '-';
        $icao = $get('icaoCode') ?: '-';
        $city = $get('municipality') ?: '-';
        $country = $get('countryCode') ?: 'ID';
        $lat = $get('latitude');
        $lng = $get('longitude');
        $elevation = $get('elevationFt');
        $operator = $get('operator');
        $service = $get('scheduledService');

        $reply = $isEn
            ? "Here is detailed information regarding {$name} airport from the AeroFind RDF dataset:\n\n"
            : "Berikut informasi detail mengenai bandara {$name} dari dataset RDF AeroFind:\n\n";

        $reply .= ($isEn ? "- Official Name: " : "- Nama Resmi: ") . "{$name}\n";
        $reply .= ($isEn ? "- IATA Code: " : "- Kode IATA: ") . "{$iata}\n";
        $reply .= ($isEn ? "- ICAO Code: " : "- Kode ICAO: ") . "{$icao}\n";
        $reply .= ($isEn ? "- City/Municipality: " : "- Kota/Kabupaten: ") . "{$city} ({$country})\n";

        if ($lat !== $notAvail && $lng !== $notAvail) {
            $reply .= ($isEn ? "- Coordinates: " : "- Koordinat: ") . "{$lat}, {$lng}\n";
        }

        if ($elevation !== $notAvail) {
            $reply .= ($isEn ? "- Elevation: {$elevation} ft ASL\n" : "- Elevasi: {$elevation} kaki dpl\n");
        }

        if ($operator !== $notAvail) {
            $reply .= "- Operator (DBpedia): {$operator}\n";
        }

        if ($isEn) {
            $statusLayanan = ($service === 'yes' || $service === 'Yes') ? 'Active scheduled flights' : 'Non-scheduled / charter';
            $reply .= "- Scheduled Service: {$statusLayanan}\n\n";
            $reply .= "Data source: AeroFind RDF dataset via Apache Jena Fuseki and DBpedia.\n";
            $reply .= "Is there any other information about this airport you would like to know?";
        } else {
            $statusLayanan = ($service === 'yes' || $service === 'Yes') ? 'Aktif melayani penerbangan berjadwal' : 'Non-terjadwal / charter';
            $reply .= "- Layanan Terjadwal: {$statusLayanan}\n\n";
            $reply .= "Sumber data: Dataset RDF AeroFind melalui Apache Jena Fuseki dan DBpedia.\n";
            $reply .= "Ada informasi lain tentang bandara ini yang ingin kamu ketahui?";
        }

        return $reply;
    }

    /**
     * - Respon jumlah bandara di Indonesia.
     */
    public function formatIndonesiaCount(string $total): string
    {
        $isEn = $this->isEnglish();
        return $isEn
            ? "Based on the AeroFind RDF dataset, there are {$total} airports in Indonesia.\n\nYou can ask for the nearest airport from your city or view a profile of an airport."
            : "Berdasarkan dataset RDF AeroFind, terdapat {$total} data bandara di Indonesia.\n\nKamu dapat menanyakan bandara terdekat dari kotamu atau melihat profil salah satu bandara.";
    }

    /**
     * - Respon daftar contoh bandara di Indonesia.
     *
     * @param array<int, mixed> $bindings
     */
    public function formatIndonesiaList(array $bindings): string
    {
        $isEn = $this->isEnglish();
        if (empty($bindings)) {
            return $isEn
                ? "No Indonesian airport data found in the AeroFind RDF dataset."
                : "Belum ditemukan data bandara Indonesia pada dataset RDF AeroFind.";
        }

        $reply = $isEn
            ? "Here are some airports in Indonesia based on the AeroFind RDF dataset:\n\n"
            : "Berikut beberapa bandara di Indonesia berdasarkan dataset RDF AeroFind:\n\n";

        foreach ($bindings as $index => $item) {
            $num = $index + 1;
            $name = $item['name']['value'] ?? '-';
            $iata = $item['iataCode']['value'] ?? '-';
            $loc = $item['municipality']['value'] ?? '-';

            $reply .= $isEn
                ? "{$num}. {$name} (IATA: {$iata}) - Location: {$loc}\n"
                : "{$num}. {$name} (IATA: {$iata}) - Lokasi: {$loc}\n";
        }

        $reply .= $isEn
            ? "\nYou can ask for the profile of the airports above, for example: 'Profile {$bindings[0]['name']['value']}' or 'Airports near Medan'."
            : "\nKamu dapat menanyakan profil bandara di atas, contohnya: 'Profil {$bindings[0]['name']['value']}' atau 'Bandara terdekat dari Medan'.";

        return $reply;
    }

    /**
     * - Respon ramah jika wilayah tidak terdeteksi dari kalimat pengguna.
     */
    public function formatMissingRegionPrompt(): string
    {
        $isEn = app()->getLocale() === 'en';
        return $isEn
            ? "Which region are you looking for airports in? Please specify the name of the city or municipality, for example:\n- 'Airports near Medan'\n- 'Airports near Deli Serdang'\n- 'Recommended airports in Bandung'"
            : "Kamu ingin mencari bandara di daerah mana? Silakan sebutkan nama kota atau kabupaten yang kamu tuju, contohnya:\n- 'Bandara dekat Medan'\n- 'Bandara yang dekat Deli Serdang'\n- 'Rekomendasi bandara di Bandung'";
    }

    /**
     * - Respon sapaan bersahabat dan on-point.
     */
    public function formatGreeting(): string
    {
        $isEn = app()->getLocale() === 'en';
        return $isEn
            ? "Hello! I am AeroBot, virtual assistant of AeroFind. I can help you find airport profile information, airport lists, and nearby airport recommendations based on cities or municipalities in Southeast Asia.\n\nYou can ask using everyday language, for example:\n- 'What are the airports near Medan?'\n- 'Kualanamu airport profile'\n- 'How many airports in Indonesia?'\n\nHow can I help you today?"
            : "Halo! Saya AeroBot, asisten virtual AeroFind. Saya dapat membantu kamu mencari informasi profil bandara, daftar bandara, dan rekomendasi bandara terdekat berdasarkan kota atau kabupaten di Asia Tenggara.\n\nKamu bisa bertanya dengan bahasa sehari-hari, misalnya:\n- 'Bandara yang dekat Medan apa saja?'\n- 'Profil bandara Kualanamu'\n- 'Berapa jumlah bandara di Indonesia?'\n\nAda yang bisa saya bantu untuk kamu hari ini?";
    }

    /**
     * - Respon bantuan fitur aplikasi.
     */
    public function formatHelp(): string
    {
        $isEn = app()->getLocale() === 'en';
        if ($isEn) {
            return "AeroFind is a Semantic Web-based airport search and directory application. Features you can ask directly:\n\n" .
                "1. Nearby Recommendations: Ask 'airports near Medan' or 'which airports in Deli Serdang'.\n" .
                "2. Airport Profile: Ask 'Kualanamu profile' or 'CGK airport info'.\n" .
                "3. National Data: Ask 'how many airports in Indonesia' or 'list of airports in Indonesia'.\n" .
                "4. Airports by Island: Ask 'airports in Bali island', 'airports in Luzon', or 'Kalimantan island airports'.\n" .
                "5. Geospatial Map: Open the Airport Map menu to explore airport distributions interactively.\n\n" .
                "Feel free to type your question.";
        }

        return "AeroFind adalah aplikasi pencarian dan direktori bandara berbasis Web Semantik. Fitur yang bisa kamu tanyakan langsung:\n\n" .
            "1. Rekomendasi Terdekat: Tanyakan 'bandara dekat Medan' atau 'mana bandara daerah Deli Serdang'.\n" .
            "2. Profil Bandara: Tanyakan 'profil Kualanamu' atau 'info bandara CGK'.\n" .
            "3. Data Nasional: Tanyakan 'berapa jumlah bandara di Indonesia' atau 'daftar bandara di Indonesia'.\n" .
            "4. Bandara per Pulau: Tanyakan 'bandara di pulau Bali', 'airport di Luzon', atau 'bandara pulau Kalimantan'.\n" .
            "5. Peta Geospasial: Buka menu Peta Bandara untuk melihat persebaran bandara secara interaktif.\n\n" .
            "Silakan ketik pertanyaan yang kamu inginkan.";
    }

    /**
     * - Respon fallback jika pesan belum dikenali.
     */
    public function formatFallback(): string
    {
        $isEn = app()->getLocale() === 'en';
        return $isEn
            ? "Sorry, I did not understand that question. You can try asking things like:\n- 'Nearest airport to Medan City'\n- 'Kualanamu Airport profile'\n- 'What airports are in Deli Serdang Regency?'\n- 'How many airports in Indonesia?'"
            : "Maaf, saya belum memahami pertanyaan tersebut. Kamu dapat mencoba bertanya seperti:\n- 'Bandara terdekat dari Kota Medan'\n- 'Profil Bandara Kualanamu'\n- 'Bandara apa saja di Kabupaten Deli Serdang?'\n- 'Berapa jumlah bandara di Indonesia?'";
    }
}