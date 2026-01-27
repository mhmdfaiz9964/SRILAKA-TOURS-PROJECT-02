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
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('grn_number')->nullable()->after('invoice_number');
            $table->decimal('broker_cost', 15, 2)->default(0)->after('paid_amount');
            $table->decimal('transport_cost', 15, 2)->default(0)->after('broker_cost');
            $table->decimal('duty_cost', 15, 2)->default(0)->after('transport_cost');
            $table->decimal('kuli_cost', 15, 2)->default(0)->after('duty_cost');
        });

        Schema::create('purchase_investors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->string('investor_name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['grn_number', 'broker_cost', 'transport_cost', 'duty_cost', 'kuli_cost']);
        });

        Schema::dropIfExists('purchase_investors');
    }
};
