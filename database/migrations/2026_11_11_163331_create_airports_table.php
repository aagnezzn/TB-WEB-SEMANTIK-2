<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id('airport_id');
            $table->string('name');
            $table->string('iata_code')->nullable();
            $table->string('icao_code')->nullable();
            $table->string('type')->nullable();
            $table->string('municipality')->nullable();
            $table->string('iso_country')->nullable();
            $table->decimal('latitude_deg', 10, 7)->nullable();
            $table->decimal('longitude_deg', 10, 7)->nullable();
            $table->string('scheduled_service')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};