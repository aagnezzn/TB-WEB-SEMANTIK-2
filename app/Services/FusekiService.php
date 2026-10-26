<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FusekiService
{
    public function select(string $query): array
    {
        $baseUrl = rtrim(
            config('services.fuseki.base_url'),
            '/'
        );

        $dataset = config('services.fuseki.dataset');
        $queryPath = config('services.fuseki.query_path', '/query');

        $url = "{$baseUrl}/{$dataset}{$queryPath}";

        $response = Http::timeout(10)
            ->accept('application/sparql-results+json')
            ->asForm()
            ->post($url, [
                'query' => $query,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Kueri SPARQL ke Fuseki gagal.'
            );
        }

        return $response->json();
    }
}