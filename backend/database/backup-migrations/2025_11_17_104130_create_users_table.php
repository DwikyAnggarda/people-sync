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
        // Intentionally left blank. The canonical `users` table definition lives in
        // `0001_01_01_000000_create_users_table.php` to avoid duplicate creations.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to roll back.
    }
};
