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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');;
            $table->foreignId('ticket_issue_type_id')->constrained();
            $table->foreignId('ticket_status_type_id')->constrained();
            $table->foreignId('response_level_type_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->string('executive_summary')->nullable();
            $table->string('stated_issue');
            $table->dateTime('raised_on');
            $table->dateTime('closed_on')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
