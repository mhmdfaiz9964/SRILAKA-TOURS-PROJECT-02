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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('original_sale_id')->nullable()->after('customer_id');
            $table->foreign('original_sale_id')->references('id')->on('sales')->onDelete('set null');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('original_item_id')->nullable()->after('product_id');
            $table->foreign('original_item_id')->references('id')->on('sale_items')->onDelete('set null');
        });

        Schema::table('purchase_investors', function (Blueprint $table) {
            if (Schema::hasTable('investors')) {
                $table->unsignedBigInteger('investor_id')->nullable()->after('purchase_id');
                $table->foreign('investor_id')->references('id')->on('investors')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['original_sale_id']);
            $table->dropColumn('original_sale_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['original_item_id']);
            $table->dropColumn('original_item_id');
        });

        Schema::table('purchase_investors', function (Blueprint $table) {
            $table->dropForeign(['investor_id']);
            $table->dropColumn('investor_id');
        });
    }
};
