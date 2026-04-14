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
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number'); // ← Hapus ->unique() di sini
            $table->string('title');
            $table->text('description');
            
            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by_admin')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('category_id')->constrained('ticket_categories');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Admin notes
            $table->text('admin_notes')->nullable();
            
            // Status & Priority
            $table->enum('status', ['new', 'in_progress', 'waiting_response', 'resolved', 'closed'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable();
            $table->enum('urgency_level', ['low', 'medium', 'high', 'critical'])->nullable()->comment('Client urgency indication - not official priority');
            
            // Event & Location
            $table->string('event_name')->nullable();
            $table->string('venue')->nullable();
            $table->string('area')->nullable();
            
            // Timestamps
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('priority');
            $table->index('user_id');
            $table->index('assigned_to');
        });

        // Add unique constraint that respects soft deletes
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Partial index (paling efisien)
            DB::statement('
                CREATE UNIQUE INDEX tickets_ticket_number_unique 
                ON tickets (ticket_number) 
                WHERE deleted_at IS NULL
            ');
        } else {
            // MySQL: Generated column
            DB::statement('
                ALTER TABLE tickets 
                ADD COLUMN ticket_number_unique VARCHAR(255) 
                AS (IF(deleted_at IS NULL, ticket_number, NULL)) STORED
            ');
            
            DB::statement('
                CREATE UNIQUE INDEX tickets_ticket_number_unique 
                ON tickets (ticket_number_unique)
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS tickets_ticket_number_unique');
        } else {
            DB::statement('DROP INDEX IF EXISTS tickets_ticket_number_unique');
            DB::statement('ALTER TABLE tickets DROP COLUMN IF EXISTS ticket_number_unique');
        }
        
        Schema::dropIfExists('tickets');
    }
};

