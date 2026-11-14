<?php

use App\Services\FusekiService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\AirportMapController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\AirportRecommendationController;
use App\Http\Controllers\ChatbotController;

// Rute Kategori Lokasi
Route::get('/category/location', [AirportController::class, 'locationCategory'])->name('category.location');

// Rute Kategori Telepon
Route::get('/category/phone', [AirportController::class, 'phoneCategory'])->name('category.phone');

// Rute Kategori Operasional
Route::get('/category/operational', [AirportController::class, 'operationalCategory'])->name('category.operational');

// Rute Utama & Pencarian (Satu rute untuk halaman utama dan pencarian)
Route::get('/', [AirportController::class, 'index'])->name('home');

// Rute Pencarian Bandara
Route::get('/airports/map', [AirportMapController::class, 'map'])->name('airports.map');

// Rute Halaman Detail Bandara
Route::get('/airports/{id}', [AirportController::class, 'show'])->name('airports.show');

// Rute Chatbot
Route::view('/chatbot', 'chatbot')->name('chatbot');

// API untuk komunikasi chatbot dengan Laravel dan Fuseki
Route::post('/chatbot/send', [
    ChatbotController::class,
    'chat'
])->name('chatbot.send');

// Rute Tes Fuseki (Bawaan)
Route::get('/test-fuseki', function (FusekiService $fuseki) {
    $query = <<<'SPARQL'
PREFIX af: <https://aerofind.example/id/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT ?airport ?name
WHERE {
    ?airport a af:Airport ;
             rdfs:label ?name .
}
LIMIT 10
SPARQL;

    try {
        $result = $fuseki->select($query);

        return response()->json([
            'status' => 'success',
            'message' => 'Laravel berhasil terhubung ke Fuseki.',
            'data' => $result['results']['bindings'] ?? [],
        ]);
    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'status' => 'error',
            'message' => 'Query ke Fuseki gagal.',
        ], 500);
    }
});

// Rute untuk Rekomendasi Bandara Terdekat
Route::get(
    '/recommendations/nearest',
    [AirportRecommendationController::class, 'nearest']
);

// Rute untuk daftar bandara untuk peta
Route::get('/api/airports', [
    AirportMapController::class,
    'index'
]);

// Rute untuk Rekomendasi Bandara Terdekat (versi lengkap)
Route::get('/api/regions', [
    AirportMapController::class,
    'regions'
]);
