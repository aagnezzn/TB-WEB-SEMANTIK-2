<?php

namespace Tests\Feature;

use Tests\TestCase;

class LanguageSwitcherTest extends TestCase
{
    /**
     * Memastikan route /lang/en memvalidasi, menyimpan ke session, dan redirect.
     */
    public function test_switch_to_english(): void
    {
        $response = $this->get('/lang/en');

        $response->assertRedirect('/');
        $response->assertSessionHas('locale', 'en');
    }

    /**
     * Memastikan route /lang/id memvalidasi, menyimpan ke session, dan redirect.
     */
    public function test_switch_to_indonesian(): void
    {
        $response = $this->get('/lang/id');

        $response->assertRedirect('/');
        $response->assertSessionHas('locale', 'id');
    }

    /**
     * Memastikan locale yang tidak valid ditolak dengan HTTP 400.
     */
    public function test_invalid_locale_aborts(): void
    {
        $response = $this->get('/lang/fr');

        $response->assertStatus(400);
    }

    /**
     * Memastikan halaman utama menampilkan teks Bahasa Inggris ketika session locale adalah 'en'.
     */
    public function test_english_locale_renders_english_text(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get('/?search=kua');

        $response->assertStatus(200);
        $response->assertSee('Search Results for: "kua"', false);
        $response->assertSee('Clear Search');
        $response->assertSee('View Details');

        // Test section "What You Can Do" in English
        $homeResponse = $this->withSession(['locale' => 'en'])->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('What You Can Do');
        $homeResponse->assertSee('All your ASEAN airport information needs in one place.');
        $homeResponse->assertSee('Airport Directory');
        $homeResponse->assertSee('Explore the complete catalog by type, country, and elevation.');
        $homeResponse->assertSee('Nearby Airports');
        $homeResponse->assertSee('Click any point on the map to find the 5 nearest airports.');
        $homeResponse->assertSee('Route Navigation');
        $homeResponse->assertSee('Compare travel times by car, motorcycle, bicycle, and walking.');
        $homeResponse->assertSee('Ask AeroBot');
        $homeResponse->assertSee('Find airport information through conversational AI.');
    }

    /**
     * Memastikan halaman utama menampilkan teks Bahasa Indonesia secara default.
     */
    public function test_indonesian_locale_renders_indonesian_text_by_default(): void
    {
        $response = $this->get('/?search=kua');

        $response->assertStatus(200);
        $response->assertSee('Hasil Pencarian untuk: "kua"', false);
        $response->assertSee('Hapus Pencarian');
        $response->assertSee('Lihat Detail');

        // Test section "Apa yang Bisa Anda Lakukan" in Indonesian
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Apa yang Bisa Anda Lakukan?');
        $homeResponse->assertSee('Semua kebutuhan informasi bandara ASEAN Anda dalam satu tempat.');
        $homeResponse->assertSee('Direktori Bandara');
        $homeResponse->assertSee('Jelajahi katalog lengkap berdasarkan tipe, negara, dan elevasi.');
        $homeResponse->assertSee('Bandara Terdekat');
        $homeResponse->assertSee('Klik titik mana saja di peta untuk menemukan 5 bandara terdekat.');
        $homeResponse->assertSee('Navigasi Rute');
        $homeResponse->assertSee('Bandingkan waktu tempuh dengan mobil, motor, sepeda, dan berjalan kaki.');
        $homeResponse->assertSee('Tanya AeroBot');
        $homeResponse->assertSee('Temukan informasi bandara melalui AI percakapan.');
    }

    /**
     * Memastikan tombol switcher ID dan EN muncul di navbar.
     */
    public function test_language_switcher_buttons_exist_in_navbar(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('/lang/id');
        $response->assertSee('/lang/en');
        $response->assertSee('ID');
        $response->assertSee('EN');
    }
}
