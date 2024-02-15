<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scrape_records', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('details_url')->nullable();
            $table->json('urls');
            $table->json('extract_rules');
            $table->json('results')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrape_records');
    }
};
