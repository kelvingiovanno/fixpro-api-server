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
            $table->uuid('id')->primary();
            $table->uuid('ticket_id')->nullable();
            $table->string('resource_type');
            $table->string('resource_name');
            $table->double('resource_size');
            $table->string('previewable_on');
            $table->softDeletes();
            
            $table->foreign('ticket_id')->references('id')->on('tickets');
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
