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
            $table->dropForeign(['cheque_id']);
            $table->string('cheque_type')->nullable()->after('cheque_id');
            $table->index(['cheque_type', 'cheque_id']);
            
            // Note: We are keeping cheque_id as unsignedBigInteger (which it likely is)
            // Ideally we would rename it or just use it as part of the morph.
            // Since it was already foreignId, it is bigInt.
            // Morph columns usually are (name)_type and (name)_id.
            // So we have cheque_id and cheque_type. Perfect.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['cheque_type', 'cheque_id']);
            $table->dropColumn('cheque_type');
            
            // Re-adding the foreign key might fail if there are now polymorphic records,
            // but we add it back for rollback correctness if data allows.
            // This is a "best effort" rollback.
            $table->foreign('cheque_id')->references('id')->on('cheques')->onDelete('cascade');
        });
    }
};
