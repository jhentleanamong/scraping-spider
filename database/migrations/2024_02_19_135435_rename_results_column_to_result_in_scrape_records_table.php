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
        Schema::table('scrape_records', function (Blueprint $table) {
            $table->renameColumn('results', 'result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scrape_records', function (Blueprint $table) {
            $table->renameColumn('result', 'results');
        });
    }
};
