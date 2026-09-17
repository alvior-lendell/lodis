<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('min_password_length')->default(8);
            $table->boolean('require_uppercase')->default(true);
            $table->boolean('require_numeric')->default(true);
            $table->boolean('require_special_char')->default(true);
            $table->unsignedSmallInteger('password_expiry_days')->default(90);
            $table->unsignedTinyInteger('max_login_attempts')->default(5);
            $table->unsignedSmallInteger('lockout_duration_minutes')->default(15);
            $table->timestamps();
        });

        // Seed default security settings record (ID 1)
        DB::table('security_settings')->insert([
            'id' => 1,
            'min_password_length' => 8,
            'require_uppercase' => true,
            'require_numeric' => true,
            'require_special_char' => true,
            'password_expiry_days' => 90,
            'max_login_attempts' => 5,
            'lockout_duration_minutes' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_settings');
    }
};