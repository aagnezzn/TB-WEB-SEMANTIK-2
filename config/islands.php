<?php

/*
|--------------------------------------------------------------------------
| Pemetaan Pulau Bandara ASEAN
|--------------------------------------------------------------------------
|
| Dataset bandara tidak memiliki atribut pulau. Pulau ditentukan berurutan:
|   1. Nama pulau pada kolom municipality ("... Rote Island").
|   2. Kotak koordinat (bounds) untuk pulau kecil, dibatasi per negara.
|   3. Pulau bawaan dari kode wilayah (iso_region).
|
*/

return [

    /*
    | Alias pencarian chatbot. Kunci adalah nama pulau baku yang disimpan
    | sebagai af:island di RDF.
    */
    'aliases' => [
        'Sumatra' => ['sumatera', 'sumatra'],
        'Jawa' => ['jawa', 'java'],
        'Bali' => ['bali'],
        'Borneo' => ['kalimantan', 'borneo'],
        'Sulawesi' => ['sulawesi', 'celebes'],
        'Papua' => ['papua', 'new guinea', 'irian', 'irian jaya'],
        'Timor' => ['timor', 'timor leste', 'east timor'],
        'Lombok' => ['lombok'],
        'Sumbawa' => ['sumbawa'],
        'Flores' => ['flores'],
        'Sumba' => ['sumba'],
        'Rote' => ['rote'],
        'Alor' => ['alor'],
        'Nusa Tenggara Barat' => ['nusa tenggara barat', 'ntb'],
        'Nusa Tenggara Timur' => ['nusa tenggara timur', 'ntt'],
        'Maluku' => ['maluku', 'moluccas', 'ambon'],
        'Maluku Utara' => ['maluku utara', 'halmahera'],
        'Kepulauan Riau' => ['kepulauan riau', 'kepri', 'riau islands'],
        'Bangka' => ['bangka'],
        'Belitung' => ['belitung', 'belitong'],
        'Batam' => ['batam'],
        'Bintan' => ['bintan'],
        'Nias' => ['nias'],
        'Luzon' => ['luzon', 'luson'],
        'Mindanao' => ['mindanao'],
        'Palawan' => ['palawan'],
        'Panay' => ['panay'],
        'Negros' => ['negros'],
        'Cebu' => ['cebu'],
        'Leyte' => ['leyte'],
        'Samar' => ['samar'],
        'Bohol' => ['bohol'],
        'Mindoro' => ['mindoro'],
        'Semenanjung Malaya' => ['semenanjung malaya', 'semenanjung malaysia', 'malay peninsula', 'peninsular malaysia', 'malaya'],
        'Daratan Asia Tenggara' => ['daratan asia tenggara', 'daratan', 'mainland', 'indochina', 'indocina'],
        'Singapura' => ['singapura', 'singapore'],
        'Labuan' => ['labuan'],
        'Penang' => ['penang', 'pinang'],
        'Langkawi' => ['langkawi'],
        'Phuket' => ['phuket'],
        'Ko Samui' => ['ko samui', 'koh samui', 'samui'],
        'Phu Quoc' => ['phu quoc'],
        'Natuna Besar' => ['natuna besar', 'natuna'],
        'Yamdena' => ['yamdena', 'saumlaki'],
        'Sipura' => ['sipura', 'mentawai'],
        'Wangi-wangi' => ['wangi-wangi', 'wangi wangi'],
        'Sangihe' => ['sangihe'],
        'Tablas' => ['tablas'],
        'Tioman' => ['tioman'],
        'Pangkor' => ['pangkor'],
        'Karimunjawa' => ['karimunjawa'],
        'Siargao' => ['siargao'],
        'Biak' => ['biak'],
        'Waigeo' => ['waigeo', 'raja ampat'],
        'Ternate' => ['ternate'],
        'Selayar' => ['selayar'],
        'Simeulue' => ['simeulue'],
        'Batanes' => ['batanes'],
        'Masbate' => ['masbate'],
        'Catanduanes' => ['catanduanes'],
        'Romblon' => ['romblon'],
        'Camiguin' => ['camiguin'],
        'Sulu' => ['sulu', 'jolo'],
        'Tawi-Tawi' => ['tawi-tawi', 'tawi tawi'],
        'Con Dao' => ['con dao'],
    ],

    /*
    | Nama pulau pada municipality yang perlu disatukan ke nama baku.
    */
    'municipality_names' => [
        'borneo' => 'Borneo',
        'sumatra' => 'Sumatra',
        'java' => 'Jawa',
        'timor' => 'Timor',
        'new guinea' => 'Papua',
    ],

    /*
    | Kotak koordinat [pulau, minLat, maxLat, minLon, maxLon, negara].
    | Dievaluasi berurutan; letakkan pulau yang lebih kecil lebih dulu.
    */
    'bounds' => [
        ['Batam', 0.95, 1.30, 103.90, 104.20, 'ID'],
        ['Bintan', 0.70, 1.20, 104.20, 104.70, 'ID'],
        ['Natuna Besar', 3.70, 4.30, 108.00, 108.50, 'ID'],
        ['Bangka', -3.20, -1.40, 105.00, 106.90, 'ID'],
        ['Belitung', -3.30, -2.30, 107.40, 108.40, 'ID'],
        ['Nias', 0.90, 1.40, 97.00, 97.95, 'ID'],
        ['Simeulue', 2.30, 2.90, 95.70, 96.50, 'ID'],
        ['Karimunjawa', -6.00, -5.60, 110.20, 110.80, 'ID'],
        ['Lombok', -9.00, -8.20, 115.80, 116.85, 'ID'],
        ['Sumbawa', -9.00, -8.00, 116.85, 119.30, 'ID'],
        ['Rote', -11.20, -10.30, 122.60, 123.50, 'ID'],
        ['Sumba', -10.60, -9.20, 119.00, 120.50, 'ID'],
        ['Alor', -8.60, -8.00, 124.30, 125.00, 'ID'],
        ['Flores', -9.00, -8.00, 119.80, 123.20, 'ID'],
        ['Timor', -10.50, -8.30, 123.30, 127.40, 'ID'],
        ['Selayar', -6.60, -5.70, 120.20, 120.80, 'ID'],
        ['Ternate', 0.60, 1.00, 127.20, 127.50, 'ID'],
        ['Biak', -1.40, -0.70, 135.70, 136.50, 'ID'],
        ['Waigeo', -0.80, 0.30, 130.20, 131.40, 'ID'],
        ['Langkawi', 6.10, 6.50, 99.60, 99.95, 'MY'],
        ['Penang', 5.20, 5.50, 100.20, 100.40, 'MY'],
        ['Tioman', 2.70, 2.95, 104.10, 104.30, 'MY'],
        ['Pangkor', 4.10, 4.30, 100.50, 100.70, 'MY'],
        ['Phuket', 7.70, 8.30, 98.20, 98.50, 'TH'],
        ['Phu Quoc', 9.90, 10.50, 103.80, 104.10, 'VN'],
        ['Con Dao', 8.60, 8.80, 106.50, 106.80, 'VN'],
        ['Siargao', 9.60, 10.20, 125.80, 126.40, 'PH'],
    ],

    /*
    | Pulau bawaan per kode wilayah (iso_region).
    */
    'regions' => [
        // Indonesia
        'ID-AC' => 'Sumatra', 'ID-SU' => 'Sumatra', 'ID-SB' => 'Sumatra', 'ID-RI' => 'Sumatra',
        'ID-JA' => 'Sumatra', 'ID-SS' => 'Sumatra', 'ID-BE' => 'Sumatra', 'ID-LA' => 'Sumatra',
        'ID-BB' => 'Bangka', 'ID-KR' => 'Kepulauan Riau',
        'ID-JK' => 'Jawa', 'ID-BT' => 'Jawa', 'ID-JB' => 'Jawa', 'ID-JT' => 'Jawa',
        'ID-YO' => 'Jawa', 'ID-JI' => 'Jawa',
        'ID-BA' => 'Bali', 'ID-NB' => 'Nusa Tenggara Barat', 'ID-NT' => 'Nusa Tenggara Timur',
        'ID-KB' => 'Borneo', 'ID-KI' => 'Borneo', 'ID-KS' => 'Borneo', 'ID-KT' => 'Borneo', 'ID-KU' => 'Borneo',
        'ID-GO' => 'Sulawesi', 'ID-SA' => 'Sulawesi', 'ID-SG' => 'Sulawesi', 'ID-SN' => 'Sulawesi',
        'ID-ST' => 'Sulawesi', 'ID-SR' => 'Sulawesi',
        'ID-MA' => 'Maluku', 'ID-MU' => 'Maluku Utara',
        'ID-PA' => 'Papua', 'ID-PB' => 'Papua', 'ID-PD' => 'Papua', 'ID-PP' => 'Papua',
        'ID-PS' => 'Papua', 'ID-PT' => 'Papua',

        // Malaysia
        'MY-01' => 'Semenanjung Malaya', 'MY-02' => 'Semenanjung Malaya', 'MY-03' => 'Semenanjung Malaya',
        'MY-04' => 'Semenanjung Malaya', 'MY-05' => 'Semenanjung Malaya', 'MY-06' => 'Semenanjung Malaya',
        'MY-07' => 'Semenanjung Malaya', 'MY-08' => 'Semenanjung Malaya', 'MY-09' => 'Semenanjung Malaya',
        'MY-10' => 'Semenanjung Malaya', 'MY-11' => 'Semenanjung Malaya', 'MY-14' => 'Semenanjung Malaya',
        'MY-12' => 'Borneo', 'MY-13' => 'Borneo', 'MY-15' => 'Labuan',

        // Brunei, Singapura, Timor Leste
        'BN-BM' => 'Borneo', 'BN-BE' => 'Borneo', 'BN-TE' => 'Borneo', 'BN-TU' => 'Borneo',
        'SG-01' => 'Singapura', 'SG-02' => 'Singapura', 'SG-03' => 'Singapura',
        'SG-04' => 'Singapura', 'SG-05' => 'Singapura',
        'TL-BA' => 'Timor', 'TL-CO' => 'Timor', 'TL-DI' => 'Timor', 'TL-OE' => 'Timor',

        // Filipina
        'PH-00' => 'Luzon', 'PH-ALB' => 'Luzon', 'PH-BTN' => 'Batanes', 'PH-CAG' => 'Luzon',
        'PH-CAS' => 'Luzon', 'PH-ILN' => 'Luzon', 'PH-ISA' => 'Luzon', 'PH-PAM' => 'Luzon',
        'PH-ZMB' => 'Luzon', 'PH-CAT' => 'Catanduanes', 'PH-MAS' => 'Masbate',
        'PH-MDC' => 'Mindoro', 'PH-ROM' => 'Romblon', 'PH-PLW' => 'Palawan',
        'PH-AKL' => 'Panay', 'PH-CAP' => 'Panay', 'PH-ILI' => 'Panay',
        'PH-NEC' => 'Negros', 'PH-NER' => 'Negros', 'PH-CEB' => 'Cebu', 'PH-BOH' => 'Bohol',
        'PH-LEY' => 'Leyte', 'PH-NSA' => 'Samar', 'PH-WSA' => 'Samar', 'PH-CAM' => 'Camiguin',
        'PH-SLU' => 'Sulu', 'PH-TAW' => 'Tawi-Tawi',
        'PH-BTC' => 'Mindanao', 'PH-DVO' => 'Mindanao', 'PH-MDN' => 'Mindanao', 'PH-MSC' => 'Mindanao',
        'PH-MSR' => 'Mindanao', 'PH-SCO' => 'Mindanao', 'PH-SUN' => 'Mindanao', 'PH-ZAN' => 'Mindanao',
        'PH-ZAS' => 'Mindanao', 'PH-ZMC' => 'Mindanao',
    ],

    /*
    | Negara daratan utama: seluruh wilayahnya masuk "Daratan Asia Tenggara"
    | kecuali pulau yang tertangkap aturan sebelumnya.
    */
    'mainland_countries' => ['TH', 'KH', 'LA', 'MM', 'VN'],
    'mainland_name' => 'Daratan Asia Tenggara',
];
