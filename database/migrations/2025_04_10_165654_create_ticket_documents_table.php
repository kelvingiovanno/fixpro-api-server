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
        Schema::create('ticket_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_id');
            $table->foreign('ticket_id')->references('id')->on('tickets');
            $table->string('resource_type');
            $table->string('resource_name');
            $table->string('resource_size');
            $table->string('resource_path')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supportive_ticket_documents');
    }
};
