<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sla_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            
            // SLA Times (in minutes)
            $table->integer('response_time_sla')->default(60); // 1 hour
            $table->integer('resolution_time_sla')->default(1440); // 24 hours
            
            // Actual Times
            $table->integer('actual_response_time')->nullable();
            $table->integer('actual_resolution_time')->nullable();
            
            // Status
            $table->boolean('response_sla_met')->nullable();
            $table->boolean('resolution_sla_met')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_trackings');
    }
};
