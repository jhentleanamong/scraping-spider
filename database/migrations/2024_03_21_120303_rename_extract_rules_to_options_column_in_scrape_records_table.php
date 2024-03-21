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
            $table->renameColumn('extract_rules', 'options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scrape_records', function (Blueprint $table) {
            $table->renameColumn('options', 'extract_rules');
        });
    }
};
