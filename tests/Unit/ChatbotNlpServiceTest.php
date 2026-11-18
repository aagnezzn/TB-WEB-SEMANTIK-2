<?php

namespace Tests\Unit;

use App\Models\Region;
use App\Services\ChatbotNlpService;
use PHPUnit\Framework\TestCase;

class ChatbotNlpServiceTest extends TestCase
{
    protected ChatbotNlpService $nlp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->nlp = new ChatbotNlpService();
    }

    public function test_normalizes_slang_and_abbreviations(): void
    {
        $input = "bndara yg dkat medan dmn ya?";
        $normalized = $this->nlp->normalize($input);

        $this->assertStringContainsString('bandara', $normalized);
        $this->assertStringContainsString('yang', $normalized);
        $this->assertStringContainsString('dekat', $normalized);
        $this->assertStringContainsString('di mana', $normalized);
        $this->assertStringContainsString('medan', $normalized);
    }

    public function test_extracts_airport_alias_and_code(): void
    {
        $input = "profil kualanamu dong";
        $normalized = $this->nlp->normalize($input);
        $airport = $this->nlp->extractAirportEntity($normalized);

        $this->assertNotNull($airport);
        $this->assertEquals('KNO', $airport['iata']);
        $this->assertEquals('Kualanamu International Airport', $airport['name']);

        $input2 = "info bandara cgk";
        $normalized2 = $this->nlp->normalize($input2);
        $airport2 = $this->nlp->extractAirportEntity($normalized2);

        $this->assertNotNull($airport2);
        $this->assertEquals('CGK', $airport2['iata']);
    }

    public function test_intent_detection_nearest_airport(): void
    {
        $normalized = $this->nlp->normalize("mana bandara daerah deli serdang yg dkat");
        $airport = $this->nlp->extractAirportEntity($normalized);

        $region = new Region();
        $region->region_name = 'Deli Serdang';
        $region->latitude = 3.4287;
        $region->longitude = 98.6761;

        $intent = $this->nlp->detectIntent($normalized, $region, $airport);
        $this->assertEquals('NEAREST_AIRPORT', $intent);
    }

    public function test_intent_detection_count_and_list(): void
    {
        $normalizedCount = $this->nlp->normalize("berapa banyak bandara di indonesia?");
        $intentCount = $this->nlp->detectIntent($normalizedCount, null, null);
        $this->assertEquals('INDONESIA_AIRPORTS_COUNT', $intentCount);

        $normalizedList = $this->nlp->normalize("daftar bandara di indonesia");
        $intentList = $this->nlp->detectIntent($normalizedList, null, null);
        $this->assertEquals('INDONESIA_AIRPORTS_LIST', $intentList);
    }

    public function test_intent_detection_greeting(): void
    {
        $normalized = $this->nlp->normalize("halo aerobot selamat pagi");
        $intent = $this->nlp->detectIntent($normalized, null, null);
        $this->assertEquals('GREETING', $intent);
    }

    public function test_no_emojis_and_uses_kamu(): void
    {
        $greeting = $this->nlp->formatGreeting();
        $this->assertStringContainsString('kamu', $greeting);
        $this->assertStringNotContainsString('sob', $greeting);
        $this->assertStringNotContainsString('bro', $greeting);

        // Pastikan tidak ada emoji
        $hasEmoji = (bool) preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $greeting);
        $this->assertFalse($hasEmoji, 'Greeting tidak boleh mengandung emoji.');

        $missing = $this->nlp->formatMissingRegionPrompt();
        $this->assertStringContainsString('kamu', $missing);
        $hasEmojiMissing = (bool) preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $missing);
        $this->assertFalse($hasEmojiMissing, 'Prompt missing region tidak boleh mengandung emoji.');
    }
}

