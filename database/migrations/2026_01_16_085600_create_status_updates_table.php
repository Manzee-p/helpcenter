<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_board_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Admin yang update
            
            $table->text('message'); // "Tim sedang memperbaiki sistem listrik"
            $table->enum('update_type', [
                'investigating',
                'update',
                'resolved'
            ])->default('update');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_updates');
    }
};