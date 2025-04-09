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
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('ticket_issue_type_id')->constrained();
            $table->foreignId('ticket_status_type_id')->constrained();
            $table->foreignId('response_level_type_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->string('stated_issue');
            $table->dateTime('raised_on');
            $table->dateTime('closed_on')->nullable();
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
