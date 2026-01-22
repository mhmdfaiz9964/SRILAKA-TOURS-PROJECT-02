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
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('cheque_number');
            $table->date('cheque_date');
            $table->foreignId('bank_id')->constrained();
            $table->foreignId('cheque_reason_id')->constrained();
            $table->decimal('amount', 15, 2);
            $table->string('payer_name');
            $table->string('payment_status')->default('pending'); // paid, partial paid, pending
            $table->string('cheque_status')->default('processing'); // processing, approved, rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
