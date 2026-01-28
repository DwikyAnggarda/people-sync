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

    /**
     * Run the migrations.
     * 
     * Fix: Change model_id from bigint to uuid type in permission tables
     * to support User model with UUID primary key.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        // Fix model_has_permissions table
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelMorphKey) {
            // Drop the primary key first (includes model_id)
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');

            // Drop the index
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
        });

        // Change column type using raw SQL (PostgreSQL)
        DB::statement("ALTER TABLE {$tableNames['model_has_permissions']} ALTER COLUMN {$modelMorphKey} TYPE uuid USING {$modelMorphKey}::text::uuid");

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelMorphKey) {
            // Recreate the index
            $table->index([$modelMorphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');

            // Recreate the primary key
            $table->primary(['permission_id', $modelMorphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        // Fix model_has_roles table
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelMorphKey) {
            // Drop the primary key first (includes model_id)
            $table->dropPrimary('model_has_roles_role_model_type_primary');

            // Drop the index
            $table->dropIndex('model_has_roles_model_id_model_type_index');
        });

        // Change column type using raw SQL (PostgreSQL)
        DB::statement("ALTER TABLE {$tableNames['model_has_roles']} ALTER COLUMN {$modelMorphKey} TYPE uuid USING {$modelMorphKey}::text::uuid");

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelMorphKey) {
            // Recreate the index
            $table->index([$modelMorphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');

            // Recreate the primary key
            $table->primary(['role_id', $modelMorphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        // Revert model_has_permissions table
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelMorphKey) {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
        });

        DB::statement("ALTER TABLE {$tableNames['model_has_permissions']} ALTER COLUMN {$modelMorphKey} TYPE bigint USING NULL");

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelMorphKey) {
            $table->index([$modelMorphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->primary(['permission_id', $modelMorphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        // Revert model_has_roles table
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelMorphKey) {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->dropIndex('model_has_roles_model_id_model_type_index');
        });

        DB::statement("ALTER TABLE {$tableNames['model_has_roles']} ALTER COLUMN {$modelMorphKey} TYPE bigint USING NULL");

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelMorphKey) {
            $table->index([$modelMorphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->primary(['role_id', $modelMorphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
        });
    }
};
