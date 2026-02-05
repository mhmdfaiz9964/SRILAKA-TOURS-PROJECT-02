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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->decimal('amount', 15, 2);
            $table->string('paid_by')->nullable(); // Who paid / authorised / or relevant person
            $table->text('notes')->nullable();
            $table->string('payment_method'); // cash, cheque, bank_transfer
            
            // Cheque details
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->foreignId('bank_id')->nullable()->constrained('banks');
            $table->string('payer_name')->nullable(); // Or payee name since it is expense? User said "payer name" but for expense cheque out, it is us paying? Or maybe "Payee Name"? 
            // User request: "when cheqe s.lected show cheqe number 6 digits , cheqe date , amount , bank , payer name"
            // If it is an expense, we are GIVING the cheque. So the "Payer" is US. 
            // However, maybe they mean "Payee Name" (who receives it). 
            // But the user strictly asked for "payer name". I will stick to "payer_name" column but label it appropriately or clarify. 
            // Actually, if I write a cheque, the "Payer" is me. 
            // Maybe they mean "Payee Name" (the person I am paying). 
            // I will add `payer_name` as requested, but might also add `cheque_amount` if needed (though `amount` covers it).
            
            $table->date('expense_date')->default(now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
