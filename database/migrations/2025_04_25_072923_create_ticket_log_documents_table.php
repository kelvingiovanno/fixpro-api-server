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
        Schema::create('ticket_log_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_log_id');
            $table->string('resource_type');
            $table->string('resource_name');
            $table->string('resource_size');
            $table->string('resource_path')->unique();
            $table->softDeletes();
            
            $table->foreign('ticket_log_id')->references('id')->on('ticket_logs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_log_documents');
    }
};
