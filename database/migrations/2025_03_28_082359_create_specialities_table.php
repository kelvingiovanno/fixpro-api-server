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
        Schema::create('specialties', function (Blueprint $table) {
            $table->uuid('member_id');
            $table->uuid('issue_id')->nullable();

            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete('set null'); 
            $table->foreign('issue_id')->references('id')->on('ticket_issue_types')->cascadeOnDelete('set null');

            $table->primary(['member_id', 'issue_id']);
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