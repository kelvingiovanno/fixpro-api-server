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
            $table->uuid('id')->primary();
            $table->uuid('role_id')->nullable();
            $table->string('name');
            $table->string('title')->nullable();
            $table->dateTime('member_since');
            $table->dateTime('member_until');

            $table->foreign('role_id')->references('id')->on('users_role')->onDelete('set null');
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
