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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('google_id')->nullable(); // Google OAuth
            $table->string('phone')->nullable();
            $table->string('password')->nullable(); // ← PERBAIKAN: nullable untuk Google OAuth
            $table->enum('role', ['client', 'admin', 'vendor'])->default('client');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            
            // === VENDOR FIELDS ===
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('specialization')->nullable(); // e.g., "Sound System, Lighting"
            
            // === WORK INFO ===
            $table->string('position', 100)->nullable();
            $table->string('company', 200)->nullable();
            $table->string('department', 100)->nullable();
            
            // === EMERGENCY CONTACT ===
            $table->string('emergency_contact', 20)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relation', 100)->nullable();
            
            // === ADDRESS ===
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            
            // === PERSONAL INFO ===
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('nik', 16)->nullable()->unique();
            $table->text('bio')->nullable();
            
            // === SYSTEM FIELDS ===
            $table->string('avatar')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->json('preferences')->nullable();
            $table->json('notification_settings')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};