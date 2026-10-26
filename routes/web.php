<?php

use App\Services\FusekiService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('home', 'home')->name('home');

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