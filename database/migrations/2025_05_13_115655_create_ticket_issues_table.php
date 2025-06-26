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
        Schema::create('ticket_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('issue_id')->nullable();
            $table->uuid('ticket_id')->nullable();
            $table->text('work_description')->nullable();

            $table->foreign('issue_id')->references('id')->on('ticket_issue_types')->onDelete('set null');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('set null');
            
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_type_ticket');
    }
};
