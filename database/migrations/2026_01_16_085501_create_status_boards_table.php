<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_boards', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number')->unique(); // e.g., INC-2024-001
            
            // Public Information
            $table->string('title'); // "Gangguan Listrik di Hall A"
            $table->text('description'); // Detail masalah
            $table->enum('category', [
                'power_outage',      // Gangguan Listrik
                'technical_issue',   // Masalah Teknis
                'facility_issue',    // Masalah Fasilitas
                'network_issue',     // Gangguan Jaringan
                'other'
            ]);
            
            // Location/Area affected
            $table->string('affected_area')->nullable(); // "Hall A", "Ruang VIP", etc.
            
            // Status
            $table->enum('status', [
                'investigating', // Sedang Diselidiki
                'identified',    // Masalah Teridentifikasi
                'monitoring',    // Dalam Pemantauan
                'resolved'       // Selesai
            ])->default('investigating');
            
            // Severity
            $table->enum('severity', [
                'critical',  // Kritis
                'high',      // Tinggi
                'medium',    // Sedang
                'low'        // Rendah
            ])->default('medium');
            
            // Timeline
            $table->timestamp('started_at'); // Kapan masalah dimulai
            $table->timestamp('resolved_at')->nullable(); // Kapan selesai
            
            // Admin who created/manages this
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Visibility
            $table->boolean('is_public')->default(true); // Apakah ditampilkan ke publik
            $table->boolean('is_pinned')->default(false); // Pin di atas
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('severity');
            $table->index('category');
            $table->index('is_public');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_boards');
    }
};