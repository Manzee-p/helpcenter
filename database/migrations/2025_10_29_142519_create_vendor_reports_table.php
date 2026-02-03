<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendor_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->enum('period_type', ['weekly', 'monthly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_tickets')->default(0);
            $table->integer('resolved_tickets')->default(0);
            $table->integer('pending_tickets')->default(0);
            $table->decimal('avg_response_time', 8, 2)->nullable(); // in minutes
            $table->decimal('avg_resolution_time', 8, 2)->nullable(); // in minutes
            $table->decimal('sla_compliance_rate', 5, 2)->nullable(); // percentage
            $table->json('tickets_by_category')->nullable();
            $table->json('tickets_by_priority')->nullable();
            $table->timestamps();
            
            $table->index(['vendor_id', 'period_type']);
            $table->unique(['vendor_id', 'period_start', 'period_end']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_reports');
    }
};
