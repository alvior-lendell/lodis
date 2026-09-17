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
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('employee_id')->nullable()->index();
            $table->string('otp');
            $table->json('payload');
            $table->unsignedTinyInteger('resend_attempts')->default(0);
            $table->boolean('is_used')->default(false)->index();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            // Optional foreign key constraint to employees table
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};