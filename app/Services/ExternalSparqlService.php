<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Klien SPARQL untuk endpoint eksternal (DBpedia) yang dipakai
 * untuk memperkaya data bandara dari dataset AeroFind sendiri.
 */
class ExternalSparqlService
{
    public const DBPEDIA_ENDPOINT = 'https://dbpedia.org/sparql';

    /**
     * Membangun pola graf DBpedia. Variabel ?wikiIri harus sudah terikat sebelum pola
     * dievaluasi. Bila kode ICAO diberikan, resource juga dicocokkan lewat
     * dbo:icaoLocationIdentifier untuk judul Wikipedia yang tidak cocok.
     */
    public static function enrichmentPattern(?string $icaoCode = null): string
    {
        $match = '{ ?dbpedia foaf:isPrimaryTopicOf ?wikiIri }';

        if ($icaoCode !== null && preg_match('/^[A-Za-z0-9]{3,8}$/', $icaoCode)) {
            $icao = strtoupper($icaoCode);
            $match .= " UNION { VALUES ?icaoLiteral { \"{$icao}\" \"{$icao}\"@en } ?dbpedia dbo:icaoLocationIdentifier ?icaoLiteral }";
        }

        return $match.'
OPTIONAL { ?dbpedia dbo:abstract ?abstract . FILTER(lang(?abstract) = "en") }
OPTIONAL { ?dbpedia dbo:operator ?operatorIri . ?operatorIri rdfs:label ?operator . FILTER(lang(?operator) = "en") }
OPTIONAL { ?dbpedia dbo:openingDate ?opened }
OPTIONAL { ?dbpedia dbo:thumbnail ?thumbnail }';
    }

    public const PREFIXES = <<<'SPARQL'
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dbo: <http://dbpedia.org/ontology/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SPARQL;

    /**
     * Menjalankan kueri SELECT ke endpoint SPARQL eksternal.
     *
     * @return array<string, mixed>
     */
    public function select(string $query, string $endpoint = self::DBPEDIA_ENDPOINT): array
    {
        $response = Http::timeout(10)
            ->connectTimeout(4)
            ->accept('application/sparql-results+json')
            ->asForm()
            ->post($endpoint, ['query' => $query]);

        $response->throw();

        return $response->json() ?? [];
    }

    /**
     * Mengambil data tambahan bandara dari DBpedia berdasarkan tautan Wikipedia.
     *
     * @return array{dbpedia: ?string, abstract: ?string, operator: ?string, opened: ?string, thumbnail: ?string}|null
     */
    public function enrichmentForWikipediaLink(?string $wikipediaLink, ?string $icaoCode = null): ?array
    {
        if (empty($wikipediaLink)) {
            return null;
        }

        $wikiIri = preg_replace('#^https://#i', 'http://', trim($wikipediaLink));

        if (! filter_var($wikiIri, FILTER_VALIDATE_URL) || preg_match('/[<>"\s]/', $wikiIri)) {
            return null;
        }

        return Cache::remember('dbpedia.enrichment.'.md5($wikiIri.'|'.$icaoCode), now()->addDay(), function () use ($wikiIri, $icaoCode) {
            try {
                $query = self::PREFIXES."\nSELECT ?dbpedia ?abstract ?operator ?opened ?thumbnail WHERE {\n"
                    ."BIND(<{$wikiIri}> AS ?wikiIri)\n".self::enrichmentPattern($icaoCode)."\n} LIMIT 1";

                $bindings = $this->select($query)['results']['bindings'] ?? [];
            } catch (Throwable $e) {
                report($e);

                return null;
            }

            return self::normalizeBinding($bindings[0] ?? null);
        });
    }

    /**
     * @param  array<string, array{value: string}>|null  $binding
     * @return array{dbpedia: ?string, abstract: ?string, operator: ?string, opened: ?string, thumbnail: ?string}|null
     */
    public static function normalizeBinding(?array $binding): ?array
    {
        if (empty($binding)) {
            return null;
        }

        return [
            'dbpedia' => $binding['dbpedia']['value'] ?? null,
            'abstract' => $binding['abstract']['value'] ?? null,
            'operator' => $binding['operator']['value'] ?? null,
            'opened' => $binding['opened']['value'] ?? null,
            'thumbnail' => $binding['thumbnail']['value'] ?? null,
        ];
    }
}
