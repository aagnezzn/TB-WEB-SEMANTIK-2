<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->string('region_id', 50)->primary();
            $table->string('region_name');
            $table->string('region_type', 20)->nullable();
            $table->string('country_code', 5);
            $table->string('country_name')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('source')->nullable();
            $table->integer('source_year')->nullable();
            $table->text('license')->nullable();

            $table->index(['country_code', 'region_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
