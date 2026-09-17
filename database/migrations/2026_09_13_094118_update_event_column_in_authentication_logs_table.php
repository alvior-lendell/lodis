<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authentication_logs', function (Blueprint $table) {
            $table->string('event', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('authentication_logs', function (Blueprint $table) {
            $table->enum('event', ['login', 'logout'])->change();
        });
    }
};