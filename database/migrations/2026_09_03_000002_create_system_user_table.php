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
        Schema::create('system_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('system_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['Superadmin', 'Admin', 'Employee', 'None'])->default('None');
            $table->boolean('has_access')->default(false);
            $table->unsignedSmallInteger('custom_order')->nullable();
            $table->timestamps();

            // Prevent duplicate assignments for the same employee and system
            $table->unique(['user_id', 'system_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_user');
    }
};