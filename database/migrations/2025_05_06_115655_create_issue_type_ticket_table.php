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
        Schema::create('issue_type_ticket', function (Blueprint $table) {
            $table->uuid('issue_type_id')->nullable();
            $table->uuid('ticket_id')->nullable();

            $table->foreign('issue_type_id')->references('id')->on('ticket_issue_types')->onDelete('set null');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('set null');
            
            $table->primary(['issue_type_id', 'ticket_id']);
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
