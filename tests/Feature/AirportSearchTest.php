<?php

namespace Tests\Feature;

use Tests\TestCase;

class AirportSearchTest extends TestCase
{
    /**
     * Memastikan pencarian potongan kata "kua" menampilkan Bandara Kualanamu dan datanya.
     */
    public function test_search_partial_kua_returns_kualanamu(): void
    {
        $response = $this->get('/?search=kua');

        $response->assertStatus(200);
        $response->assertSee('Hasil Pencarian untuk: "kua"', false);
        $response->assertSee('Kualanamu International Airport');
        $response->assertSee('WIMM');
        $response->assertSee('KNO');
        $response->assertSee('Beringin');
        $response->assertSee('Indonesia');
    }

    /**
     * Memastikan pencarian bersifat case-insensitive (contoh "KUA" huruf besar).
     */
    public function test_search_is_case_insensitive(): void
    {
        $response = $this->get('/?search=KUA');

        $response->assertStatus(200);
        $response->assertSee('Kualanamu International Airport');
    }

    /**
     * Memastikan pencarian berdasarkan kode IATA "KNO".
     */
    public function test_search_by_iata_code(): void
    {
        $response = $this->get('/?search=kno');

        $response->assertStatus(200);
        $response->assertSee('Kualanamu International Airport');
    }

    /**
     * Memastikan pencarian berdasarkan kode ICAO / ident "WIMM".
     */
    public function test_search_by_icao_code(): void
    {
        $response = $this->get('/?search=wimm');

        $response->assertStatus(200);
        $response->assertSee('Kualanamu International Airport');
    }

    /**
     * Memastikan pencarian berdasarkan kota / municipality "Beringin".
     */
    public function test_search_by_municipality(): void
    {
        $response = $this->get('/?search=beringin');

        $response->assertStatus(200);
        $response->assertSee('Kualanamu International Airport');
    }

    /**
     * Memastikan pencarian berdasarkan kata kunci / keywords "Medan".
     */
    public function test_search_by_keywords(): void
    {
        $response = $this->get('/?search=medan');

        $response->assertStatus(200);
        $response->assertSee('Kualanamu International Airport');
    }

    /**
     * Memastikan pencarian berdasarkan potongan nama negara "indo" dan kode ISO "id".
     */
    public function test_search_by_country_and_iso(): void
    {
        $responseIso = $this->get('/?search=id');
        $responseIso->assertStatus(200);
        $responseIso->assertSee('Indonesia');

        $responseCountry = $this->get('/?search=indo');
        $responseCountry->assertStatus(200);
        $responseCountry->assertSee('Indonesia');
    }

    /**
     * Memastikan tidak ada emoji pada halaman beranda saat pencarian dilakukan.
     */
    public function test_no_emojis_in_search_view(): void
    {
        $response = $this->get('/?search=kua');
        $content = $response->getContent();

        // Cek surrogate pairs emoji dan simbol emoji umum
        $hasEmoji = preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F1E0}-\x{1F1FF}]/u', $content);
        $this->assertEquals(0, $hasEmoji, 'View hasil pencarian tidak boleh mengandung emoji.');
    }

    /**
     * Memastikan halaman kategori lokasi bebas emoji bendera.
     */
    public function test_no_emojis_in_category_location(): void
    {
        $response = $this->get('/category/location');
        $response->assertStatus(200);

        $content = $response->getContent();
        $hasEmoji = preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F1E0}-\x{1F1FF}]/u', $content);
        $this->assertEquals(0, $hasEmoji, 'View kategori lokasi tidak boleh mengandung emoji.');
    }

    /**
     * Memastikan halaman detail bandara bebas emoji dan dapat diakses dengan case-insensitive.
     */
    public function test_airport_detail_page_case_insensitive_and_no_emoji(): void
    {
        $responseUpper = $this->get('/airports/WIMM');
        $responseUpper->assertStatus(200);
        $responseUpper->assertSee('Kualanamu International Airport');

        $responseLower = $this->get('/airports/wimm');
        $responseLower->assertStatus(200);
        $responseLower->assertSee('Kualanamu International Airport');

        $content = $responseUpper->getContent();
        $hasEmoji = preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F1E0}-\x{1F1FF}]/u', $content);
        $this->assertEquals(0, $hasEmoji, 'View detail bandara tidak boleh mengandung emoji.');
    }

    /**
     * Memastikan pencarian di direktori bandara juga mendukung substring match.
     */
    public function test_directory_partial_search(): void
    {
        $response = $this->get('/data-bandara?q=kua');
        $response->assertStatus(200);
        $response->assertSee('Kualanamu International Airport');
    }

    /**
     * Memastikan typo seperti "kualamanu" terdeteksi kemiripannya dan menampilkan saran "Kualanamu".
     */
    public function test_search_typo_suggests_kualanamu(): void
    {
        $response = $this->get('/?search=kualamanu');

        $response->assertStatus(200);
        $response->assertSee('Apakah maksud Anda:');
        $response->assertSee('Kualanamu');
        $response->assertSee('search=Kualanamu');
        $response->assertSee('Kualanamu International Airport');
    }

    /**
     * Memastikan pesan saran typo mendukung mode bilingual Bahasa Inggris.
     */
    public function test_search_typo_suggests_bilingual_english(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get('/?search=kualamanu');

        $response->assertStatus(200);
        $response->assertSee('Did you mean:');
        $response->assertSee('Kualanamu');
        $response->assertSee('search=Kualanamu');
        $response->assertSee('Kualanamu International Airport');
    }

    /**
     * Memastikan tampilan hasil deteksi typo bebas dari emotikon / emoji.
     */
    public function test_no_emojis_in_typo_suggestion_view(): void
    {
        $response = $this->get('/?search=kualamanu');
        $content = $response->getContent();

        $hasEmoji = preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F1E0}-\x{1F1FF}]/u', $content);
        $this->assertEquals(0, $hasEmoji, 'View saran typo tidak boleh mengandung emoji.');
    }
}
