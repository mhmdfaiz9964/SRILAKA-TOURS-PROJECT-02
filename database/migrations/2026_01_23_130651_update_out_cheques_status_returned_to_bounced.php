<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add 'bounced' to the ENUM (keeping 'returned' temporarily)
        DB::statement("ALTER TABLE out_cheques MODIFY COLUMN status ENUM('sent', 'realized', 'returned', 'bounced') NOT NULL DEFAULT 'sent'");
        
        // Step 2: Update existing 'returned' values to 'bounced'
        DB::statement("UPDATE out_cheques SET status = 'bounced' WHERE status = 'returned'");
        
        // Step 3: Remove 'returned' from the ENUM
        DB::statement("ALTER TABLE out_cheques MODIFY COLUMN status ENUM('sent', 'realized', 'bounced') NOT NULL DEFAULT 'sent'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add 'returned' back to the ENUM
        DB::statement("ALTER TABLE out_cheques MODIFY COLUMN status ENUM('sent', 'realized', 'returned', 'bounced') NOT NULL DEFAULT 'sent'");
        
        // Step 2: Revert 'bounced' values back to 'returned'
        DB::statement("UPDATE out_cheques SET status = 'returned' WHERE status = 'bounced'");
        
        // Step 3: Remove 'bounced' from the ENUM
        DB::statement("ALTER TABLE out_cheques MODIFY COLUMN status ENUM('sent', 'realized', 'returned') NOT NULL DEFAULT 'sent'");
    }
};
