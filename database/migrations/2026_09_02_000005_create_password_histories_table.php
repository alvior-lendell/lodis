<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('password');
            $table->timestamps();
        });

        // Add history limit column to security_settings
        if (Schema::hasTable('security_settings')) {
            Schema::table('security_settings', function (Blueprint $table) {
                $table->unsignedTinyInteger('password_history_limit')->default(3)->after('password_expiry_days');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_histories');
        if (Schema::hasTable('security_settings')) {
            Schema::table('security_settings', function (Blueprint $table) {
                $table->dropColumn('password_history_limit');
            });
        }
    }
};