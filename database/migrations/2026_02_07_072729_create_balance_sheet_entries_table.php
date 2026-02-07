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
        Schema::create('balance_sheet_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable(); // Can be nullable if we treat it as a snapshot or current state
            $table->enum('category', ['asset', 'liability', 'equity']);
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_sheet_entries');
    }
};
