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
        Schema::create('in_cheques', function (Blueprint $table) {
            $table->id();
            $table->date('cheque_date');
            $table->decimal('amount', 15, 2);
            $table->string('cheque_number', 6);
            $table->foreignId('bank_id')->constrained('banks');
            $table->string('payer_name');
            $table->text('notes')->nullable();
            $table->enum('status', ['received', 'deposited', 'transferred_to_third_party', 'realized', 'returned'])->default('received');
            $table->string('third_party_name')->nullable();
            $table->timestamps();
        });

        Schema::create('out_cheques', function (Blueprint $table) {
            $table->id();
            $table->date('cheque_date');
            $table->decimal('amount', 15, 2);
            $table->string('cheque_number', 6);
            $table->foreignId('bank_id')->constrained('banks');
            $table->string('payee_name');
            $table->text('notes')->nullable();
            $table->enum('status', ['sent', 'realized', 'returned'])->default('sent');
            $table->timestamps();
        });

        Schema::create('third_party_cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('in_cheque_id')->constrained('in_cheques')->onDelete('cascade');
            $table->string('third_party_name');
            $table->date('transfer_date');
            $table->enum('status', ['received', 'realized', 'returned'])->default('received');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('third_party_cheques');
        Schema::dropIfExists('out_cheques');
        Schema::dropIfExists('in_cheques');
    }
};
