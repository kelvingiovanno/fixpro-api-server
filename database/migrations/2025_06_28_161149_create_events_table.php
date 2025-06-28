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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('calender_id');
            $table->uuid('ticket_id');

            $table->string('summary');
            $table->string('description');
            $table->dateTime('start');
            $table->dateTime('end');

            $table->foreign('calender_id')->references('id')->on('calenders')->onDelete('set null');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('set null');
            
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_events');
    }
};
