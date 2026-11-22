# TB Web Semantik – AeroSearch ASEAN

Aplikasi Laravel untuk menjelajahi bandara se-ASEAN berbasis data semantik.

## Komponen semantik
- **File RDF**: `semantic-data/airports.ttl` (dibuat oleh `export_airports_to_ttl.php`).
- **SPARQL endpoint sendiri**: Apache Jena Fuseki, dataset `airports` (sumber data utama aplikasi).
- **SPARQL endpoint eksternal**: DBpedia (`App\Services\ExternalSparqlService`), diintegrasikan lewat kueri federasi `SERVICE` di Fuseki dengan cadangan pemanggilan langsung.
- **RDFa**: halaman detail bandara (schema.org/Airport). **Open Graph Protocol**: `resources/views/layouts/og.blade.php`.
- **Layanan lain**: OpenStreetMap (Leaflet). PostgreSQL hanya untuk data pendukung dan cadangan.

## Menjalankan Fuseki
```
docker compose up -d fuseki
```
Lalu buka http://localhost:3030, masuk dengan user `admin`, buka dataset `airports`, dan unggah `semantic-data/airports.ttl`
(atau dari folder `/staging` di kontainer: `/jena/bin/tdbloader`/upload lewat UI).
Data yang dimuat: `semantic-data/airports.ttl` (bandara) dan `semantic-data/regions.ttl` (kota/kabupaten).
Buat ulang berkas TTL dengan `php export_airports_to_ttl.php` dan `php export_regions_to_ttl.php`.

Atur `FUSEKI_BASE_URL`, `FUSEKI_DATASET`, `FUSEKI_QUERY_PATH` di `.env`.
Jika Fuseki mati, aplikasi memakai `database/data/dataset_bandara_tubes.json` sebagai cadangan.
