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
        // Tables yang perlu ditambahkan user tracking dan soft deletes
        $tables = [
            'assets',
            'employees',
            'assets_mutation',
            'assets_monitoring',
            'assets_maintenance',
            'assets_disposals',
            'asset_purchases',
            'assets_requests',
            'asset_request_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Add created_by, updated_by, deleted_by (jika belum ada)
                if (!Schema::hasColumn($tableName, 'created_by')) {
                    $table->uuid('created_by')->nullable()->after('updated_at');
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                }

                if (!Schema::hasColumn($tableName, 'updated_by')) {
                    $table->uuid('updated_by')->nullable()->after('created_by');
                    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                }

                if (!Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->uuid('deleted_by')->nullable()->after('updated_by');
                    $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
                }

                // Add soft deletes (jika belum ada)
                if (!Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes()->after('deleted_by');
                }
            });
        }

        // Add indexes for better performance
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->index('created_by', "idx_{$tableName}_created_by");
                }
                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->index('deleted_at', "idx_{$tableName}_deleted_at");
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'assets',
            'employees',
            'assets_mutation',
            'assets_monitoring',
            'assets_maintenance',
            'assets_disposals',
            'asset_purchases',
            'assets_requests',
            'asset_request_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop indexes first
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropIndex("idx_{$tableName}_created_by");
                }
                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->dropIndex("idx_{$tableName}_deleted_at");
                }

                // Drop foreign keys
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropForeign(["{$tableName}_created_by_foreign"]);
                }
                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->dropForeign(["{$tableName}_updated_by_foreign"]);
                }
                if (Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->dropForeign(["{$tableName}_deleted_by_foreign"]);
                }

                // Drop columns
                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
                if (Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->dropColumn('deleted_by');
                }
                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->dropColumn('updated_by');
                }
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
        }
    }
};
