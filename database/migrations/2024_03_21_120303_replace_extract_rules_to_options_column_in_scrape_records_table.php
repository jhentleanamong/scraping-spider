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
            $table->dropColumn('extract_rules');
            $table->json('options')->nullable()->after('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scrape_records', function (Blueprint $table) {
            $table->json('extract_rules')->after('url');
            $table->dropColumn('options');
        });
    }
};
