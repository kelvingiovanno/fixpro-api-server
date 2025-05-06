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
        Schema::create('speciality_user', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('speciality_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete('set null'); 
            $table->foreign('speciality_id')->references('id')->on('specialities')->cascadeOnDelete('set null');

            $table->primary(['user_id', 'speciality_id']);
            $table->softDeletes();
        });
    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialty_mapping');
    }
};