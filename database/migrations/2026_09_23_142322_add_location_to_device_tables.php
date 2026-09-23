<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('device_authorizations', function (Blueprint $table) {
            $table->string('location')->nullable()->after('ip_address');
        });

        Schema::table('user_devices', function (Blueprint $table) {
            $table->string('location')->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('device_authorizations', function (Blueprint $table) {
            $table->dropColumn('location');
        });

        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};