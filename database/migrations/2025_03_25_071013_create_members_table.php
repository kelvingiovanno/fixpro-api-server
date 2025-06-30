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
        Schema::create('members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('role_id')->nullable();
            $table->string('name');
            $table->string('title')->nullable();

            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('access_token')->nullable();

            $table->dateTime('member_since')->nullable();
            $table->dateTime('member_until')->nullable();

            $table->foreign('role_id')->references('id')->on('member_roles')->onDelete('set null');
            $table->softDeletes();  
        });
    }

    /**
     * Reverse the migrations.Us 
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
