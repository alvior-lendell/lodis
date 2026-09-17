<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registered Trusted Devices
        if (!Schema::hasTable('user_devices')) {
            Schema::create('user_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('device_key')->index();
                $table->string('device_name');
                $table->string('ip_address', 45);
                $table->text('user_agent')->nullable();
                $table->timestamp('last_active_at')->nullable();
                $table->timestamps();
            });
        }

        // Real-Time Authorization Requests
        if (!Schema::hasTable('device_authorizations')) {
            Schema::create('device_authorizations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('device_key');
                $table->string('device_name');
                $table->string('ip_address', 45);
                $table->text('user_agent')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_authorizations');
        Schema::dropIfExists('user_devices');
    }
};