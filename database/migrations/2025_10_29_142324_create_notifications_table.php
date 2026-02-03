<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop table jika sudah ada (untuk development)
        Schema::dropIfExists('notifications');
        
        // Buat table baru dengan struktur yang benar
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // ticket_created, laporan_status_updated, etc
            $table->string('title');
            $table->text('message');
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->default('ticket'); // 'ticket' or 'laporan'
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes untuk performa
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};