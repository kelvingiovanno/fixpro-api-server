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
        Schema::create('work_order_documents', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('ticket_issue_id')->nullable();
            $table->string('resource_type');
            $table->string('resource_name');
            $table->string('resource_size');
            $table->string('previewable_on');
            
            $table->foreign('ticket_issue_id')->references('id')->on('ticket_issues')->onDelete('set null');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_documents');
    }
};
