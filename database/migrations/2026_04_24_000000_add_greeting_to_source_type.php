<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter column to text temporarily
        DB::statement('ALTER TABLE chat_messages ALTER COLUMN source_type TYPE VARCHAR(255);');

        // Drop the old enum type
        DB::statement('DROP TYPE IF EXISTS chat_messages_source_type_enum;');

        // Create new enum type with 'greeting' added before 'faq'
        DB::statement("CREATE TYPE chat_messages_source_type_enum AS ENUM ('greeting', 'faq', 'company', 'ai', 'fallback');");

        // Alter column back to the new enum type
        DB::statement('ALTER TABLE chat_messages ALTER COLUMN source_type TYPE chat_messages_source_type_enum USING source_type::text::chat_messages_source_type_enum;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any 'greeting' values to NULL (or handle as needed)
        DB::statement("UPDATE chat_messages SET source_type = NULL WHERE source_type = 'greeting';");

        // Alter the column back to the original enum
        DB::statement("ALTER TABLE chat_messages MODIFY COLUMN source_type ENUM('faq', 'company', 'ai', 'fallback') NULL;");
    }
};
