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
        // Update Sales table
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (!Schema::hasColumn('sales', 'salesman_name')) {
                    $table->string('salesman_name')->nullable()->after('salesman_id');
                }
                // We keep salesman_id nullable just in case, but we won't force it.
            });
        }

        // Update Purchases table
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'loading_cost')) {
                    $table->decimal('loading_cost', 15, 2)->default(0)->after('transport_cost');
                }
                if (!Schema::hasColumn('purchases', 'unloading_cost')) {
                    $table->decimal('unloading_cost', 15, 2)->default(0)->after('loading_cost');
                }
                if (!Schema::hasColumn('purchases', 'labour_cost')) {
                    $table->decimal('labour_cost', 15, 2)->default(0)->after('unloading_cost');
                }
                if (!Schema::hasColumn('purchases', 'air_ticket_cost')) {
                    $table->decimal('air_ticket_cost', 15, 2)->default(0)->after('labour_cost');
                }
                if (!Schema::hasColumn('purchases', 'other_expenses')) {
                    $table->decimal('other_expenses', 15, 2)->default(0)->after('air_ticket_cost');
                }
                // Rename kuli_cost if it exists? No, user might have data. I'll just ignore it in UI or drop it.
                // Let's leave it to avoid data loss, but we won't use it in new UI.
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (Schema::hasColumn('sales', 'salesman_name')) {
                    $table->dropColumn('salesman_name');
                }
            });
        }

        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn([
                    'loading_cost', 
                    'unloading_cost', 
                    'labour_cost', 
                    'air_ticket_cost', 
                    'other_expenses'
                ]);
            });
        }
    }
};
