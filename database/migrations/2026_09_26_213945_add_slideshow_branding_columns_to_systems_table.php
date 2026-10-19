<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->string('badge')->nullable()->after('description');
            $table->string('tag')->nullable()->after('badge');
            $table->json('features')->nullable()->after('tag');
        });
    }

    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropColumn(['badge', 'tag', 'features']);
        });
    }
};