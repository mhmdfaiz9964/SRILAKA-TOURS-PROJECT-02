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
        if (Schema::hasTable('sale_items') && !Schema::hasColumn('sale_items', 'description')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->string('description')->nullable()->after('product_id');
            });
        }
        
        if (Schema::hasTable('purchase_items') && !Schema::hasColumn('purchase_items', 'description')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->string('description')->nullable()->after('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
