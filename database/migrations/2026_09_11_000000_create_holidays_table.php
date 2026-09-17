<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->enum('type', ['regular', 'special_non_working', 'special_working', 'islamic'])->default('regular');
            $table->string('day_of_week')->nullable();
            $table->boolean('movable')->default(false);
            $table->boolean('double_holiday')->default(false);
            $table->json('double_holiday_names')->nullable();
            
            // Islamic Holiday Fields
            $table->boolean('eid_confirmed')->nullable();
            $table->date('estimated_date')->nullable();
            $table->date('confirmed_date')->nullable();
            $table->string('proclamation_ref')->nullable();
            
            // Long Weekend Information
            $table->boolean('is_part_of_long_weekend')->default(false);
            $table->json('long_weekend_details')->nullable();
            
            $table->json('source_info')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};