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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable()->unique()->index();
            $table->string('name');
            $table->string('email')->unique()->index();
            $table->string('password');
            $table->enum('role', ['Superadmin', 'Admin', 'Employee'])->default('Employee')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Foreign key linking to employees table
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
        Schema::dropIfExists('users');
    }
};