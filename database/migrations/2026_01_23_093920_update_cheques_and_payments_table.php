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
        Schema::table('cheques', function (Blueprint $table) {
            // Remove old columns
            $table->dropForeign(['cheque_reason_id']);
            $table->dropColumn('cheque_reason_id');
            $table->dropColumn('cheque_status');

            // Add new columns
            $table->string('type')->nullable(); // 3rd party, etc.
            $table->string('payee_name')->nullable();
            $table->string('third_party_name')->nullable();
            $table->string('third_party_payment_status')->default('pending'); // paid, pending
            $table->text('third_party_notes')->nullable();
            $table->string('return_reason')->nullable(); // 3rd party, JS fabric, customer
            $table->text('return_notes')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method')->nullable(); // bank_transfer, cash, cheque
            $table->foreignId('bank_id')->nullable()->constrained('banks');
            $table->string('reference_number')->nullable();
            $table->string('payment_cheque_number')->nullable();
            $table->date('payment_cheque_date')->nullable();
            $table->string('document')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropColumn(['payment_method', 'bank_id', 'reference_number', 'payment_cheque_number', 'payment_cheque_date', 'document']);
        });

        Schema::table('cheques', function (Blueprint $table) {
            $table->foreignId('cheque_reason_id')->nullable()->constrained();
            $table->string('cheque_status')->default('processing');
            $table->dropColumn(['type', 'payee_name', 'third_party_name', 'third_party_payment_status', 'third_party_notes', 'return_reason', 'return_notes']);
        });
    }
};
