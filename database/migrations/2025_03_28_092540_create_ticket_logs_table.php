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
        Schema::create('ticket_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ticket_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->foreignId('ticket_log_type_id')->constrained();
            $table->dateTime('recorded_at');
            $table->string('news');
            $table->softDeletes();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('set null');;
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_logs');
    }
};
