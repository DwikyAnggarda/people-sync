<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Neon / PostgreSQL must NOT run this migration in a transaction
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * CACHE TABLE
         */
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');          // PostgreSQL-safe
            $table->integer('expiration');
        });

        /**
         * CACHE LOCKS TABLE
         */
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop dependent tables first (best practice)
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
