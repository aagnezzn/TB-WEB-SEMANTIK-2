<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SemanticSearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('q');

        // Gabungin config endpoint Fuseki dari .env
        $endpoint = env('FUSEKI_BASE_URL', 'http://localhost:3030') 
                  . '/' . env('FUSEKI_DATASET', 'airports') 
                  . env('FUSEKI_QUERY_PATH', '/query');

    
        $sparqlQuery = "
            PREFIX af: <https://aerofind.example/id/>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            
            SELECT ?airport ?label ?countryCode ?elevationFt WHERE {
                ?airport a af:Airport ;
                         rdfs:label ?label ;
                         af:countryCode ?countryCode ;
                         af:elevationFt ?elevationFt .
                " . ($keyword ? "FILTER(REGEX(?label, '$keyword', 'i'))" : "") . "
            }
            LIMIT 50
        ";

        try {
            $response = Http::asForm()->post($endpoint, [
                'query' => $sparqlQuery,
            ]);

            $airports = [];
            if ($response->successful()) {
                $json = $response->json();
                $airports = $json['results']['bindings'] ?? [];
            }

            // Kalau lu mau tampilin dalam bentuk view/blade
            return view('map', compact('airports', 'keyword'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Connection to Jena Fuseki failed: ' . $e->getMessage());
        }
    }
}