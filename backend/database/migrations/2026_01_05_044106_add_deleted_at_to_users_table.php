<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * PostgreSQL / Neon must not run this migration in a transaction
     */
    public $withinTransaction = false;

    public function up(): void
    {
        /**
         * 1. Add soft deletes ONLY if missing
         */
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        /**
         * 2. Drop UNIQUE CONSTRAINT (not index!)
         */
        DB::statement('
            ALTER TABLE users
            DROP CONSTRAINT IF EXISTS users_email_unique
        ');

        /**
         * 3. Create partial unique index (PostgreSQL best practice)
         */
        DB::statement('
            CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique
            ON users (email)
            WHERE deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        /**
         * Remove partial unique index
         */
        DB::statement('DROP INDEX IF EXISTS users_email_unique');

        /**
         * Restore original unique constraint
         */
        DB::statement('
            ALTER TABLE users
            ADD CONSTRAINT users_email_unique UNIQUE (email)
        ');

        /**
         * Remove soft deletes if present
         */
        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
