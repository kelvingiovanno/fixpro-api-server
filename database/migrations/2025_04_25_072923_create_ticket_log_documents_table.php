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
            $table->uuid('id')->primary();
            $table->uuid('log_id')->nullable();
            $table->string('resource_type');
            $table->string('resource_name');
            $table->double('resource_size');
            $table->string('previewable_on');
            
            $table->foreign('log_id')->references('id')->on('ticket_logs');
            $table->softDeletes();
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
