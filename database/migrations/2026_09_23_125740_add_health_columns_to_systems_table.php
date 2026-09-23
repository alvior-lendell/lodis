<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->boolean('is_online')->default(true)->after('is_active');
            $table->timestamp('last_ping_at')->nullable()->after('is_online');
        });
    }

    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropColumn(['is_online', 'last_ping_at']);
        });
    }
};