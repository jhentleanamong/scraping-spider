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
            $table->dropColumn('urls');
            $table->string('url')->after('details_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scrape_records', function (Blueprint $table) {
            $table->json('urls')->after('details_url');
            $table->dropColumn('url');
        });
    }
};
