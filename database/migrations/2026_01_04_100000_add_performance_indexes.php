<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if index exists
     */
    private function indexExists($table, $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
        return !empty($indexes);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to assets table for better query performance
        Schema::table('assets', function (Blueprint $table) {
            if (!$this->indexExists('assets', 'idx_assets_condition')) {
                $table->index('condition_id', 'idx_assets_condition');
            }
            if (!$this->indexExists('assets', 'idx_assets_status')) {
                $table->index('status_id', 'idx_assets_status');
            }
            if (!$this->indexExists('assets', 'idx_assets_category')) {
                $table->index('category_id', 'idx_assets_category');
            }
            if (!$this->indexExists('assets', 'idx_assets_transaction_status')) {
                $table->index('transaction_status_id', 'idx_assets_transaction_status');
            }
            if (!$this->indexExists('assets', 'idx_assets_purchase_date')) {
                $table->index('purchase_date', 'idx_assets_purchase_date');
            }
            if (!$this->indexExists('assets', 'idx_assets_cat_cond')) {
                $table->index(['category_id', 'condition_id'], 'idx_assets_cat_cond');
            }
            if (!$this->indexExists('assets', 'idx_assets_status_cond')) {
                $table->index(['status_id', 'condition_id'], 'idx_assets_status_cond');
            }
        });

        // Add indexes to asset_taxes table
        Schema::table('asset_taxes', function (Blueprint $table) {
            if (!$this->indexExists('asset_taxes', 'idx_asset_taxes_payment_status')) {
                $table->index('payment_status', 'idx_asset_taxes_payment_status');
            }
            if (!$this->indexExists('asset_taxes', 'idx_asset_taxes_due_date')) {
                $table->index('due_date', 'idx_asset_taxes_due_date');
            }
            if (!$this->indexExists('asset_taxes', 'idx_asset_taxes_tax_year')) {
                $table->index('tax_year', 'idx_asset_taxes_tax_year');
            }
            if (!$this->indexExists('asset_taxes', 'idx_asset_taxes_status_due')) {
                $table->index(['payment_status', 'due_date'], 'idx_asset_taxes_status_due');
            }
        });

        // Add indexes to assets_mutation table
        Schema::table('assets_mutation', function (Blueprint $table) {
            if (!$this->indexExists('assets_mutation', 'idx_assets_mutation_asset_id')) {
                $table->index('assets_id', 'idx_assets_mutation_asset_id');
            }
            if (!$this->indexExists('assets_mutation', 'idx_assets_mutation_date')) {
                $table->index('mutation_date', 'idx_assets_mutation_date');
            }
            if (!$this->indexExists('assets_mutation', 'idx_assets_mutation_trans_status')) {
                $table->index('transaction_status_id', 'idx_assets_mutation_trans_status');
            }
        });

        // Add indexes to assets_monitoring table
        Schema::table('assets_monitoring', function (Blueprint $table) {
            if (!$this->indexExists('assets_monitoring', 'idx_assets_monitoring_asset_id')) {
                $table->index('assets_id', 'idx_assets_monitoring_asset_id');
            }
            if (!$this->indexExists('assets_monitoring', 'idx_assets_monitoring_date')) {
                $table->index('monitoring_date', 'idx_assets_monitoring_date');
            }
        });

        // Add indexes to assets_maintenance table
        Schema::table('assets_maintenance', function (Blueprint $table) {
            if (!$this->indexExists('assets_maintenance', 'idx_assets_maintenance_asset_id')) {
                $table->index('assets_id', 'idx_assets_maintenance_asset_id');
            }
            if (!$this->indexExists('assets_maintenance', 'idx_assets_maintenance_date')) {
                $table->index('maintenance_date', 'idx_assets_maintenance_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if ($this->indexExists('assets', 'idx_assets_condition')) {
                $table->dropIndex('idx_assets_condition');
            }
            if ($this->indexExists('assets', 'idx_assets_status')) {
                $table->dropIndex('idx_assets_status');
            }
            if ($this->indexExists('assets', 'idx_assets_category')) {
                $table->dropIndex('idx_assets_category');
            }
            if ($this->indexExists('assets', 'idx_assets_transaction_status')) {
                $table->dropIndex('idx_assets_transaction_status');
            }
            if ($this->indexExists('assets', 'idx_assets_purchase_date')) {
                $table->dropIndex('idx_assets_purchase_date');
            }
            if ($this->indexExists('assets', 'idx_assets_cat_cond')) {
                $table->dropIndex('idx_assets_cat_cond');
            }
            if ($this->indexExists('assets', 'idx_assets_status_cond')) {
                $table->dropIndex('idx_assets_status_cond');
            }
        });

        Schema::table('asset_taxes', function (Blueprint $table) {
            if ($this->indexExists('asset_taxes', 'idx_asset_taxes_payment_status')) {
                $table->dropIndex('idx_asset_taxes_payment_status');
            }
            if ($this->indexExists('asset_taxes', 'idx_asset_taxes_due_date')) {
                $table->dropIndex('idx_asset_taxes_due_date');
            }
            if ($this->indexExists('asset_taxes', 'idx_asset_taxes_tax_year')) {
                $table->dropIndex('idx_asset_taxes_tax_year');
            }
            if ($this->indexExists('asset_taxes', 'idx_asset_taxes_status_due')) {
                $table->dropIndex('idx_asset_taxes_status_due');
            }
        });

        Schema::table('assets_mutation', function (Blueprint $table) {
            if ($this->indexExists('assets_mutation', 'idx_assets_mutation_asset_id')) {
                $table->dropIndex('idx_assets_mutation_asset_id');
            }
            if ($this->indexExists('assets_mutation', 'idx_assets_mutation_date')) {
                $table->dropIndex('idx_assets_mutation_date');
            }
            if ($this->indexExists('assets_mutation', 'idx_assets_mutation_trans_status')) {
                $table->dropIndex('idx_assets_mutation_trans_status');
            }
        });

        Schema::table('assets_monitoring', function (Blueprint $table) {
            if ($this->indexExists('assets_monitoring', 'idx_assets_monitoring_asset_id')) {
                $table->dropIndex('idx_assets_monitoring_asset_id');
            }
            if ($this->indexExists('assets_monitoring', 'idx_assets_monitoring_date')) {
                $table->dropIndex('idx_assets_monitoring_date');
            }
        });

        Schema::table('assets_maintenance', function (Blueprint $table) {
            if ($this->indexExists('assets_maintenance', 'idx_assets_maintenance_asset_id')) {
                $table->dropIndex('idx_assets_maintenance_asset_id');
            }
            if ($this->indexExists('assets_maintenance', 'idx_assets_maintenance_date')) {
                $table->dropIndex('idx_assets_maintenance_date');
            }
        });
    }
};
