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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('cheque_id')->nullable()->change(); // Make nullable as not all payments are for cheques
            $table->nullableMorphs('payable'); // customer_id or supplier_id
            $table->enum('type', ['in', 'out'])->default('in'); // 'in' for customer payment, 'out' for supplier payment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
